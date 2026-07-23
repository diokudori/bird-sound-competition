<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\LiveJudgement;
use App\Http\Controllers\ContestReportController;

Route::get('/', LiveJudgement::class)->name('dashboard');
Route::get('/contest/{contestId}/export-pdf', [ContestReportController::class, 'exportPdf'])->name('contest.export-pdf');
