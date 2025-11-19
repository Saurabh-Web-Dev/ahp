<?php

namespace App\Http\Controllers\Api;

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

        // 🔍 Check if prescription already exists for this appointment
        $prescription = Prescription::where('appointment_id', $appointment->id)->first();

        if ($prescription) {
            // UPDATE instead of creating new
            $prescription->update([
                'diagnosis' => $validated['diagnosis'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Delete old medicines & re-add
            $prescription->medicines()->delete();
        } else {
            // CREATE new prescription
            $prescription = Prescription::create([
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'diagnosis' => $validated['diagnosis'],
                'notes' => $validated['notes'] ?? null,
            ]);
        }

        // Insert medicines
        foreach ($validated['medicines'] as $med) {
            $prescription->medicines()->create($med);
        }

        return response()->json([
            'message' => $prescription->wasRecentlyCreated
                ? 'Prescription saved successfully'
                : 'Prescription updated successfully',
        ]);
    }

    public function getByAppointment($appointmentId)
    {
        $prescription = Prescription::where('appointment_id', $appointmentId)
            ->with('medicines')
            ->first();

        return response()->json($prescription);
    }

}
