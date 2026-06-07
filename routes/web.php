<?php

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\DatasetLabelController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AnalysisController::class, 'index'])->name('analysis.index');
Route::post('/analyze', [AnalysisController::class, 'store'])->name('analysis.store');
Route::get('/runs/{analysisRun}', [AnalysisController::class, 'show'])->name('analysis.show');
Route::get('/dataset/labels', [DatasetLabelController::class, 'index'])->name('dataset.labels');
Route::post('/dataset/labels', [DatasetLabelController::class, 'store'])->name('dataset.labels.store');
