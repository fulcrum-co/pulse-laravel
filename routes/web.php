<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactNoteController;
use App\Http\Controllers\ContactMetricController;
use App\Http\Controllers\ResourceSuggestionController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\StrategyController;
use App\Http\Controllers\FocusAreaController;
use App\Http\Controllers\ObjectiveController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AlertController;

// Public routes
Route::get('/', function () {
    return view('home');
})->name('home');

// Public dashboard view (shareable reports, no auth required)
Route::get('/dashboard/{token}', [ReportController::class, 'publicView'])->name('reports.public');

// Public survey response route (for SMS/email links)
Route::get('/surveys/{survey}/respond/{attempt}', function ($survey, $attempt) {
    // This will be handled by a Livewire component
    return view('surveys.respond', compact('survey', 'attempt'));
})->name('surveys.respond');

// Sinch Webhooks (no auth required)
Route::prefix('webhooks/surveys')->group(function () {
    Route::post('/sinch/voice', [App\Http\Controllers\SurveyWebhookController::class, 'handleVoice'])->name('webhooks.surveys.voice');
    Route::post('/sinch/sms', [App\Http\Controllers\SurveyWebhookController::class, 'handleSms'])->name('webhooks.surveys.sms');
});

// Guest routes (only accessible when not logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Protected routes (only accessible when logged in)
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Contacts (Students, Teachers, Parents)
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/students/{student}', [ContactController::class, 'show'])->name('contacts.show');
    Route::get('/contacts/teachers/{teacher}', [ContactController::class, 'showTeacher'])->name('contacts.teacher');
    Route::get('/contacts/parents/{parent}', [ContactController::class, 'showParent'])->name('contacts.parent');

    // Contact Notes API
    Route::get('/api/contacts/{contactType}/{contactId}/notes', [ContactNoteController::class, 'index'])->name('api.notes.index');
    Route::post('/api/contacts/notes', [ContactNoteController::class, 'store'])->name('api.notes.store');
    Route::put('/api/contacts/notes/{note}', [ContactNoteController::class, 'update'])->name('api.notes.update');
    Route::delete('/api/contacts/notes/{note}', [ContactNoteController::class, 'destroy'])->name('api.notes.destroy');
    Route::get('/api/contacts/notes/{note}/audio', [ContactNoteController::class, 'audio'])->name('api.notes.audio');

    // Contact Metrics API
    Route::post('/api/metrics/time-series', [ContactMetricController::class, 'timeSeries'])->name('api.metrics.timeSeries');
    Route::post('/api/metrics', [ContactMetricController::class, 'store'])->name('api.metrics.store');
    Route::get('/api/metrics/heat-map/{student}', [ContactMetricController::class, 'heatMap'])->name('api.metrics.heatMap');
    Route::get('/api/metrics/available', [ContactMetricController::class, 'available'])->name('api.metrics.available');

    // Resource Suggestions API
    Route::get('/api/suggestions/{contactType}/{contactId}', [ResourceSuggestionController::class, 'index'])->name('api.suggestions.index');
    Route::post('/api/suggestions', [ResourceSuggestionController::class, 'store'])->name('api.suggestions.store');
    Route::put('/api/suggestions/{suggestion}/review', [ResourceSuggestionController::class, 'review'])->name('api.suggestions.review');
    Route::post('/api/suggestions/generate/{student}', [ResourceSuggestionController::class, 'generate'])->name('api.suggestions.generate');

    // Surveys
    Route::get('/surveys', [SurveyController::class, 'index'])->name('surveys.index');
    Route::get('/surveys/create', [SurveyController::class, 'create'])->name('surveys.create');
    Route::post('/surveys', [SurveyController::class, 'store'])->name('surveys.store');
    Route::get('/surveys/{survey}', [SurveyController::class, 'show'])->name('surveys.show');
    Route::get('/surveys/{survey}/edit', [SurveyController::class, 'edit'])->name('surveys.edit');
    Route::put('/surveys/{survey}', [SurveyController::class, 'update'])->name('surveys.update');
    Route::delete('/surveys/{survey}', [SurveyController::class, 'destroy'])->name('surveys.destroy');
    Route::post('/surveys/{survey}/toggle', [SurveyController::class, 'toggle'])->name('surveys.toggle');
    Route::post('/surveys/{survey}/duplicate', [SurveyController::class, 'duplicate'])->name('surveys.duplicate');

    // Survey Creation Sessions (AI-assisted)
    Route::post('/api/surveys/sessions/start', [SurveyController::class, 'startCreationSession'])->name('api.surveys.sessions.start');
    Route::post('/api/surveys/sessions/{session}/chat', [SurveyController::class, 'processCreationChat'])->name('api.surveys.sessions.chat');
    Route::post('/api/surveys/sessions/{session}/voice', [SurveyController::class, 'processCreationVoice'])->name('api.surveys.sessions.voice');
    Route::post('/api/surveys/sessions/{session}/finalize', [SurveyController::class, 'finalizeCreationSession'])->name('api.surveys.sessions.finalize');

    // Survey AI Endpoints
    Route::post('/api/surveys/ai/suggest-questions', [SurveyController::class, 'suggestQuestions'])->name('api.surveys.ai.suggest');
    Route::post('/api/surveys/ai/refine-question', [SurveyController::class, 'refineQuestion'])->name('api.surveys.ai.refine');
    Route::post('/api/surveys/ai/generate-interpretation', [SurveyController::class, 'generateInterpretation'])->name('api.surveys.ai.interpretation');

    // Question Bank
    Route::get('/api/question-bank', [SurveyController::class, 'questionBankIndex'])->name('api.question-bank.index');
    Route::post('/api/question-bank', [SurveyController::class, 'questionBankStore'])->name('api.question-bank.store');

    // Survey Templates
    Route::get('/api/survey-templates', [SurveyController::class, 'templatesIndex'])->name('api.survey-templates.index');
    Route::post('/api/survey-templates/{template}/create-survey', [SurveyController::class, 'createFromTemplate'])->name('api.survey-templates.create');

    // Survey Delivery
    Route::get('/surveys/{survey}/deliver', [SurveyController::class, 'deliverForm'])->name('surveys.deliver.form');
    Route::post('/surveys/{survey}/deliver', [SurveyController::class, 'deliver'])->name('surveys.deliver');
    Route::get('/surveys/{survey}/deliveries', [SurveyController::class, 'deliveryStatus'])->name('surveys.deliveries');

    // Strategies
    Route::get('/strategies', [StrategyController::class, 'index'])->name('strategies.index');
    Route::get('/strategies/create', [StrategyController::class, 'create'])->name('strategies.create');
    Route::post('/strategies', [StrategyController::class, 'store'])->name('strategies.store');
    Route::get('/strategies/{strategy}', [StrategyController::class, 'show'])->name('strategies.show');
    Route::get('/strategies/{strategy}/edit', [StrategyController::class, 'edit'])->name('strategies.edit');
    Route::put('/strategies/{strategy}', [StrategyController::class, 'update'])->name('strategies.update');
    Route::delete('/strategies/{strategy}', [StrategyController::class, 'destroy'])->name('strategies.destroy');
    Route::post('/strategies/{strategy}/duplicate', [StrategyController::class, 'duplicate'])->name('strategies.duplicate');
    Route::post('/strategies/{strategy}/push', [StrategyController::class, 'push'])->name('strategies.push');

    // Focus Areas
    Route::post('/strategies/{strategy}/focus-areas', [FocusAreaController::class, 'store'])->name('focus-areas.store');
    Route::put('/focus-areas/{focusArea}', [FocusAreaController::class, 'update'])->name('focus-areas.update');
    Route::delete('/focus-areas/{focusArea}', [FocusAreaController::class, 'destroy'])->name('focus-areas.destroy');
    Route::put('/focus-areas/reorder', [FocusAreaController::class, 'reorder'])->name('focus-areas.reorder');

    // Objectives
    Route::post('/focus-areas/{focusArea}/objectives', [ObjectiveController::class, 'store'])->name('objectives.store');
    Route::put('/objectives/{objective}', [ObjectiveController::class, 'update'])->name('objectives.update');
    Route::delete('/objectives/{objective}', [ObjectiveController::class, 'destroy'])->name('objectives.destroy');
    Route::put('/objectives/reorder', [ObjectiveController::class, 'reorder'])->name('objectives.reorder');

    // Activities
    Route::post('/objectives/{objective}/activities', [ActivityController::class, 'store'])->name('activities.store');
    Route::put('/activities/{activity}', [ActivityController::class, 'update'])->name('activities.update');
    Route::delete('/activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');
    Route::put('/activities/reorder', [ActivityController::class, 'reorder'])->name('activities.reorder');

    // Resources (placeholder)
    Route::get('/resources', function () {
        return view('resources.index');
    })->name('resources.index');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::get('/reports/{report}/edit', [ReportController::class, 'edit'])->name('reports.edit');
    Route::get('/reports/{report}/preview', [ReportController::class, 'preview'])->name('reports.preview');
    Route::post('/reports/{report}/duplicate', [ReportController::class, 'duplicate'])->name('reports.duplicate');
    Route::get('/reports/{report}/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
    Route::post('/reports/{report}/publish', [ReportController::class, 'publish'])->name('reports.publish');
    Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');

    // Alerts / Workflows
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/create', [AlertController::class, 'create'])->name('alerts.create');
    Route::post('/alerts', [AlertController::class, 'store'])->name('alerts.store');
    Route::get('/alerts/{workflow}', [AlertController::class, 'show'])->name('alerts.show');
    Route::get('/alerts/{workflow}/edit', [AlertController::class, 'edit'])->name('alerts.edit');
    Route::get('/alerts/{workflow}/canvas', [AlertController::class, 'canvas'])->name('alerts.canvas');
    Route::get('/alerts/{workflow}/history', [AlertController::class, 'history'])->name('alerts.history');
    Route::get('/alerts/{workflow}/executions/{execution}', [AlertController::class, 'executionDetails'])->name('alerts.execution');
    Route::post('/alerts/{workflow}/toggle', [AlertController::class, 'toggle'])->name('alerts.toggle');
    Route::post('/alerts/{workflow}/test', [AlertController::class, 'test'])->name('alerts.test');
    Route::post('/alerts/{workflow}/save', [AlertController::class, 'saveWorkflow'])->name('alerts.save');
    Route::delete('/alerts/{workflow}', [AlertController::class, 'destroy'])->name('alerts.destroy');

    // Settings (placeholder)
    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings.index');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
