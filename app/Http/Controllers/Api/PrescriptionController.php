<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Prescription;

class PrescriptionController extends Controller
{
    public function store(Request $request, $appointmentId)
    {
        $validated = $request->validate([
            'diagnosis' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'medicines' => 'required|array|min:1',
            'medicines.*.medicine_name' => 'required|string',
            'medicines.*.dosage' => 'nullable|string',
            'medicines.*.duration' => 'nullable|string',
            'medicines.*.instructions' => 'nullable|string',
        ]);

        $appointment = Appointment::findOrFail($appointmentId);

        $prescription = Prescription::create([
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'diagnosis' => $validated['diagnosis'],
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($validated['medicines'] as $med) {
            $prescription->medicines()->create($med);
        }

        return response()->json(['message' => 'Prescription saved successfully']);
    }
}
