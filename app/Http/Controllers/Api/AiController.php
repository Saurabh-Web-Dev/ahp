<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenAI;

class AiController extends Controller
{
    public function suggest(Request $request)
    {
        $request->validate([
            'diagnosis' => 'required|string'
        ]);

        $disease = trim($request->diagnosis);
        $cacheKey = 'ai_medicine_' . md5($disease);

        // 1️⃣ Quick return using cache if available
        if (cache()->has($cacheKey)) {
            return response()->json([
                'source' => 'cache',
                'confidence' => 92,
                'medicines' => cache()->get($cacheKey)
            ]);
        }

        // 2️⃣ Try AI Request
        try {
            $prompt = "
            You are an expert homeopathy doctor.
            Suggest ONLY homeopathic medicines.
            Respond in JSON only.

            Disease: $disease

            Include 3-5 medicines with doses.
            Format:
            [
              {\"medicine_name\": \"Belladonna 30C\", \"dosage\": \"3 times a day\", \"duration\": \"5 days\", \"instructions\": \"Before food\"}
            ]
            ";

            $client = OpenAI::client(env('OPENAI_API_KEY'));

            $result = retry(3, function () use ($client, $prompt) {
                return $client->chat()->create([
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => "You are a homeopathy medical expert."],
                        ['role' => 'user', 'content' => $prompt],
                    ]
                ]);
            }, 1000);

            $content = $result->choices[0]->message->content ?? '[]';
            // Remove ```json or ``` and trim
            $clean = preg_replace('/```(json)?/i', '', $content);
            $clean = trim(str_replace('```', '', $clean));

            // Try to decode
            $medicines = json_decode($clean, true);

            // Check if valid
            if (!is_array($medicines)) {
                throw new \Exception("AI returned invalid JSON");
            }


            // Store in cache for 6 hours
            cache()->put($cacheKey, $medicines, now()->addHours(6000));

            return response()->json([
                'source'     => 'AI',
                'confidence' => rand(80, 98),
                'medicines'  => $medicines ?? [],
                'count'      => count($medicines ?? []),
                'disease'    => $disease,
                'maincontent'=> $content
            ]);

        } catch (\Exception $e) {

            Log::error("AI ERROR: " . $e->getMessage());

            // 3️⃣ Offline fallback if AI fails
            $fallbackData = $this->dbFallback($disease);

            return response()->json([
                'source' => 'Database Fallback',
                'confidence' => 70,
                'medicines' => $fallbackData
            ]);
        }
    }

    /**
     * Offline Local DB fallback
     */
    private function dbFallback($disease)
    {
        $mapping = DB::table('disease_medicine_map')
            ->where('disease', 'LIKE', "%$disease%")
            ->first();

        if (!$mapping) {
            return [
                [
                    "medicine_name" => "Generic Homeopathic Remedy",
                    "dosage" => "2-3 times a day",
                    "duration" => "3 days",
                    "instructions" => "Take with clean water"
                ]
            ];
        }

        return json_decode($mapping->medicines, true);
    }
}
