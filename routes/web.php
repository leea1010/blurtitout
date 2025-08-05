<?php

use App\Http\Controllers\TherapistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Debug route
Route::get('/debug', function () {
    $therapist = App\Models\Therapist::first();
    return [
        'name' => $therapist->name,
        'specialty' => $therapist->specialty,
        'specialty_type' => gettype($therapist->specialty),
        'general_expertise' => $therapist->general_expertise,
        'general_expertise_type' => gettype($therapist->general_expertise),
        'raw_specialty' => $therapist->getRawOriginal('specialty'),
        'raw_general_expertise' => $therapist->getRawOriginal('general_expertise')
    ];
});

// Therapists routes
Route::get('/results', [TherapistController::class, 'index'])->name('therapists.index');
Route::get('/results/export', [TherapistController::class, 'export'])->name('therapists.export');
Route::resource('therapists', TherapistController::class)->except(['index']);

use Illuminate\Support\Facades\File;

Route::get('/read-result/{date?}', function ($date = null) {
    if (!$date) {
        $date = now()->format('Y_m_d');
    }

    $path = base_path("scripts/result_{$date}.txt");

    if (!File::exists($path)) {
        abort(404, "File result_{$date}.txt not found.");
    }

    $content = File::get($path);
    $data = json_decode($content, true);

    if (!$data) {
        abort(500, "Unable to parse the content of the file.");
    }

    return view('results', compact('data', 'date'));
})->name('read.result');
