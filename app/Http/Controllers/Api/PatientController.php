<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Prescription;


class PatientController extends Controller
{
    public function appointments($id)
    {
        $patient = Patient::findOrFail($id);
        $appointments = $patient->appointments()
            ->with('doctor:id,name')
            ->orderByDesc('appointment_date')
            ->get();

        return response()->json([
            'patient' => $patient,
            'appointments' => $appointments
        ]);
    }
    public function prescriptions($id)
    {
        $prescriptions = Prescription::where('patient_id', $id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($prescriptions);
    }
}
