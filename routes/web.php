<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnalysisController;

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

// Route to display the main analysis page.
Route::get('/', [AnalysisController::class, 'index'])->name('analysis.index');

// Route to handle the file upload and trigger the analysis.
Route::post('/analyze', [AnalysisController::class, 'analyze'])->name('analysis.run');// Route to trigger the Excel download.
Route::get('/export', [AnalysisController::class, 'exportReport'])->name('analysis.export');// NEW: Route to clear the session and start over.
Route::get('/clear', [AnalysisController::class, 'clearSession'])->name('analysis.clear');