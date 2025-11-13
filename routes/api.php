<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ManagerController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\DoctorDashboardController;
use App\Http\Controllers\api\PatientController;
use App\Http\Controllers\api\PrescriptionController;

// For SPA cookie-based auth: frontend should call GET /sanctum/csrf-cookie first (Sanctum route)
Route::post('/login', [AuthController::class, 'login']);              // doctor/manager login
Route::post('/patient/login', [AuthController::class, 'patientLogin']); // patient login


//////////////////////////////////
// Manager Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/patients', [ManagerController::class, 'patientList']);
    Route::post('/patients', [ManagerController::class, 'addPatient']);
    // routes/api.php
    Route::get('/patients/{id}/appointments', [PatientController::class, 'appointments']);
    Route::put('/patients/{id}', [ManagerController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/users', [AuthController::class, 'getUsersByRole']);
    Route::get('/patients/search', [ManagerController::class, 'searchPatients']);
    Route::get('/manager/dashboard-stats', [ManagerController::class, 'stats']);
});

/////////////////////////////////////////////

// Doctor Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/appointments', [AppointmentController::class, 'index']); // manager
    Route::post('/appointments', [AppointmentController::class, 'store']); // create
    Route::get('/appointments/today', [AppointmentController::class, 'todayAppointments']); // doctor
    Route::put('/appointments/{id}/status', [AppointmentController::class, 'updateStatus']); // update
    Route::get('/doctor/dashboard-stats', [DoctorDashboardController::class, 'stats']);
    Route::post('/appointments/{id}/prescription', [PrescriptionController::class, 'store']);
    Route::get('/patients/{id}/prescriptions', [PatientController::class, 'prescriptions']);
    Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
});


