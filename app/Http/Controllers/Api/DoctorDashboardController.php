<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use Carbon\Carbon;


class DoctorDashboardController extends Controller
{
    public function stats(Request $request)
    {
        $doctorId = $request->user()->id;

        // Today’s stats
        $today = Carbon::today();
        $todayPending = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->where('status', 'pending')
            ->count();

        $todayCompleted = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->where('status', 'completed')
            ->count();

        $totalAppointments = Appointment::where('doctor_id', $doctorId)
        ->whereDate('appointment_date', $today)
        ->count();

        // Last 7 days data
        $last7days = collect(range(6, 0))->map(function ($i) use ($doctorId) {
            $date = Carbon::today()->subDays($i);
            return [
                'date' => $date->format('d M'),
                'pending' => Appointment::where('doctor_id', $doctorId)
                    ->whereDate('appointment_date', $date)
                    ->where('status', 'pending')
                    ->count(),
                'completed' => Appointment::where('doctor_id', $doctorId)
                    ->whereDate('appointment_date', $date)
                    ->where('status', 'completed')
                    ->count(),
            ];
        });

        return response()->json([
            'today_pending' => $todayPending,
            'today_completed' => $todayCompleted,
            'total' => $totalAppointments,
            'last7days' => $last7days,
        ]);
    }
}
