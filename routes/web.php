<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\LiveMonitoringController;
use App\Http\Controllers\QAController;
use App\Http\Controllers\RecordingController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('calls')->name('calls.')->group(function () {
        Route::get('/', [CallController::class, 'index'])->name('index');
        Route::get('/sync', [CallController::class, 'sync'])->name('sync');
        Route::get('/search-ctm', [CallController::class, 'searchCTM'])->name('search-ctm');
        Route::get('/{ctmCallId}', [CallController::class, 'show'])->name('show');
        Route::get('/{ctmCallId}/recording', [RecordingController::class, 'show'])->name('recording');
        Route::get('/{ctmCallId}/recording/download', [RecordingController::class, 'download'])->name('recording-download');
        Route::post('/{ctmCallId}/analyze', [CallController::class, 'analyze'])->name('analyze');
        Route::post('/{ctmCallId}/transcribe', [CallController::class, 'transcribe'])->name('transcribe');
    });

    Route::prefix('qa')->name('qa.')->group(function () {
        Route::get('/logs', [QAController::class, 'logs'])->name('logs');
        Route::get('/{callId}', [QAController::class, 'show'])->name('show');
    });

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'store'])->name('settings.store');

    // Live Monitoring
    Route::prefix('live-monitoring')->name('live-monitoring.')->group(function () {
        Route::get('/', [LiveMonitoringController::class, 'index'])->name('index');
        Route::get('/session/{sessionId}', [LiveMonitoringController::class, 'session'])->name('session');
        Route::get('/session/{sessionId}/stream', [LiveMonitoringController::class, 'stream'])->name('stream');
        Route::post('/start', [LiveMonitoringController::class, 'start'])->name('start');
        Route::post('/stop/{sessionId}', [LiveMonitoringController::class, 'stop'])->name('stop');
    });

    // Supervisor Dashboard
    Route::prefix('supervisor')->name('supervisor.')->middleware('role:supervisor,admin')->group(function () {
        Route::get('/', [SupervisorController::class, 'index'])->name('index');
        Route::get('/live-stream', [SupervisorController::class, 'liveStream'])->name('live-stream');
    });

    // Agent Profiles (QA + Admin)
    Route::prefix('agents')->name('agents.')->middleware('role:qa,admin')->group(function () {
        Route::get('/', [AgentController::class, 'index'])->name('index');
        Route::get('/sync', [AgentController::class, 'sync'])->name('sync');
        Route::post('/save-filters', [AgentController::class, 'saveFilters'])->name('save-filters');
        Route::get('/search-phillies', [AgentController::class, 'searchPhillies'])->name('search-phillies');
        Route::post('/{id}/link', [AgentController::class, 'link'])->name('link');
        Route::post('/{id}/unlink', [AgentController::class, 'unlink'])->name('unlink');
        Route::get('/{id}', [AgentController::class, 'show'])->name('show');
    });

    // Knowledge Base (Admin only)
    Route::prefix('knowledge-base')->name('knowledge-base.')->middleware('role:admin')->group(function () {
        Route::get('/', [KnowledgeBaseController::class, 'index'])->name('index');
        Route::post('/', [KnowledgeBaseController::class, 'store'])->name('store');
        Route::get('/{id}', [KnowledgeBaseController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [KnowledgeBaseController::class, 'edit'])->name('edit');
        Route::put('/{id}', [KnowledgeBaseController::class, 'update'])->name('update');
        Route::delete('/{id}', [KnowledgeBaseController::class, 'destroy'])->name('destroy');
    });

    // API Routes
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/knowledge-base/search', [KnowledgeBaseController::class, 'apiSearch'])->name('knowledge-base.search');
        Route::post('/live-monitoring/add-transcript', [LiveMonitoringController::class, 'addTranscript'])->name('live-monitoring.add-transcript');
        Route::post('/live-monitoring/chat', [LiveMonitoringController::class, 'chat'])->name('live-monitoring.chat');
        Route::post('/live-monitoring/chat-stream', [LiveMonitoringController::class, 'chatStream'])->name('live-monitoring.chat-stream');
        Route::post('/live-monitoring/suggestion-stream', [LiveMonitoringController::class, 'suggestionStream'])->name('live-monitoring.suggestion-stream');
    });
});

// Webhooks (no CSRF)
Route::post('/webhooks/ctm', [WebhookController::class, 'handleCtm'])->name('webhooks.ctm');
Route::post('/webhooks/assemblyai', [WebhookController::class, 'handleAssemblyAi'])->name('webhooks.assemblyai');

require __DIR__.'/auth.php';
