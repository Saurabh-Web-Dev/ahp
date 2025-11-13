<?php


namespace App\Http\Controllers\Api;
use App\Models\Prescription;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    // 📅 List all appointments (Manager view)
    public function index()
    {
        $appointments = Appointment::with(['patient', 'doctor'])
            ->orderBy('appointment_date', 'desc')
            ->paginate(10);

        return response()->json($appointments);
    }

    // ➕ Create appointment and auto-generate daily token
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id'  => 'required|exists:users,id',
        ]);

        $today = Carbon::today()->toDateString(); // e.g. 2025-11-13
        $todayFormatted = Carbon::today()->format('Ymd'); // e.g. 20251113

        // Get latest appointment for today
        $lastAppointment = Appointment::whereDate('appointment_date', $today)
            ->orderByDesc('id')
            ->first();

        if ($lastAppointment) {
            // Extract number part from something like A-20251113-005
            preg_match('/(\d+)$/', $lastAppointment->appointment_no, $matches);
            $lastNumber = isset($matches[1]) ? intval($matches[1]) : 0;
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // ✅ Prefix style: A-20251113-001
        $appointmentNo = 'AHP-' . $todayFormatted . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // If you prefer suffix style instead:
        // $appointmentNo = 'A-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT) . '-' . $todayFormatted;

        $appointment = Appointment::create([
            'appointment_no'   => $appointmentNo,
            'patient_id'       => $request->patient_id,
            'doctor_id'        => $request->doctor_id,
            'appointment_date' => $today,
            'appointment_time' => now()->format('H:i:s'),
            'status'           => 'pending',
        ]);

        return response()->json([
            'message' => 'Appointment created successfully!',
            'appointment' => $appointment
        ]);
    }


    // 👨‍⚕️ Doctor view — today's queue
    public function todayAppointments(Request $request)
    {
        $doctorId = $request->user()->id;
        $today = Carbon::today()->toDateString();

        $appointments = Appointment::with('patient')
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->orderBy('appointment_no', 'asc')
            ->get();

        return response()->json($appointments);
    }

    // ✅ Update appointment status (e.g. completed)
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);
        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => $request->status]);
        return response()->json(['message' => 'Status updated!', 'appointment' => $appointment]);
    }

    public function show($id)
    {
        $appointment = \App\Models\Appointment::with('patient')->find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        return response()->json($appointment);
    }
}

