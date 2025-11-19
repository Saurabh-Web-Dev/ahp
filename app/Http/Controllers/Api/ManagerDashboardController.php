<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use Carbon\Carbon;

class ManagerDashboardController extends Controller
{
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
