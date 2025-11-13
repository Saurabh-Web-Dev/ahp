<?php

use Illuminate\Support\Facades\Route;

// SPA entry for app (doctor/manager)
Route::get('/app/{any?}', function () {
    return view('spa'); // resources/views/spa.blade.php (main SPA)
})->where('any', '.*');

// Separate patient login page (can use same SPA but we make separate path)
Route::get('/patient/{any?}', function () {
    return view('patient'); // loads Patient SPA entry (resources/views/patient_spa.blade.php)
})->where('any','.*');

// optional quick placeholders to confirm server routes in dev
Route::get('/', function () {
    return redirect('/app');
});

