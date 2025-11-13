<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Appointment;
use Carbon\Carbon;

class ManagerController extends Controller
{
    public function patientList(Request $request)
    {
        // Default 10 per page, or use ?per_page=20 in query string
        $query = $request->query('q');
        if($query){
            $patients = \App\Models\Patient::where('name', 'like', "%{$query}%")
            ->orWhere('patient_id', 'like', "%{$query}%")
            ->select('id', 'patient_id', 'name', 'age', 'gender', 'parent', 'location')
            ->limit(10)
            ->get();

            return response()->json($patients);
        }else{
            $perPage = $request->get('per_page', 5);

            $patients = Patient::where('manager_id', $request->user()->id)
                ->orderBy('id', 'desc')
                ->paginate(5);
            return response()->json($patients);
        }


        // Only show patients created by logged-in manager
        // $patients = Patient::where('manager_id', $request->user()->id)
        //     ->orderBy('id', 'desc')
        //     ->paginate($perPage);

        // return response()->json($patients);
    }

    /////////////////////////////////////////////
    public function addPatient(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dob' => 'required|date',
            'age' => 'required|integer|min:0',
            'gender' => 'required|in:Male,Female,Other',
            'parent' => 'required|string|max:255',
            'location' => 'required|string|max:255',
        ]);

        $validated['manager_id'] = $request->user()->id;

        $patient = Patient::create($validated);

        return response()->json([
            'message' => 'Patient added successfully',
            'patient' => $patient,
        ]);
    }

    public function searchPatients(Request $request)
    {
        $query = $request->query('q');

        $patients = \App\Models\Patient::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('patient_id', 'like', "%{$query}%");
        })
        ->select('id', 'patient_id', 'name', 'age', 'gender')
        ->limit(10)
        ->get();

        return response()->json($patients);
    }

    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);
        $patient->update($request->all());
        return response()->json(['patient' => $patient]);
    }

    public function stats()
    {
        try {
            $today = Carbon::today();

            // ✅ Today’s Stats
            $todayPending = Appointment::whereDate('appointment_date', $today)
                ->where('status', 'pending')
                ->count();

            $todayCompleted = Appointment::whereDate('appointment_date', $today)
                ->where('status', 'completed')
                ->count();

            $totalAppointments = Appointment::count();

            // ✅ Last 7 Days chart
            $last7Days = collect(range(0, 6))->map(function ($day) {
                $date = Carbon::today()->subDays($day);
                return [
                    'date' => $date->format('Y-m-d'),
                    'pending' => Appointment::whereDate('appointment_date', $date)
                        ->where('status', 'pending')
                        ->count(),
                    'completed' => Appointment::whereDate('appointment_date', $date)
                        ->where('status', 'completed')
                        ->count(),
                ];
            })->reverse()->values();

            return response()->json([
                'today_pending' => $todayPending,
                'today_completed' => $todayCompleted,
                'total' => $totalAppointments,
                'last7days' => $last7Days,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching manager dashboard data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


}
