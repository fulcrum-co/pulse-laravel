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

// Temporary route to fix avatars - visit once then remove
Route::get('/fix-avatars-temp', function () {
    // Set up error handling
    set_time_limit(300); // 5 minutes

    $output = [];
    $output[] = "Starting avatar fix...";

    try {
        // Common female first names
        $femaleNames = ['Emma','Olivia','Ava','Sophia','Isabella','Mia','Charlotte','Amelia','Harper','Evelyn','Luna','Chloe','Emily','Sarah','Maria','Jessica','Ashley','Jennifer','Amanda','Stephanie','Nicole','Michelle','Elizabeth','Heather','Melissa','Amy','Anna','Rebecca','Katherine','Christine','Rachel','Laura','Julia','Madison','Grace','Lily'];

        $maleImg = 1;
        $femaleImg = 1;
        $updated = 0;
        $errors = [];

        // Use chunking to avoid memory issues
        \App\Models\User::chunk(50, function ($users) use ($femaleNames, &$maleImg, &$femaleImg, &$updated, &$errors) {
            foreach ($users as $user) {
                try {
                    $isFemale = in_array($user->first_name, $femaleNames);

                    if ($isFemale) {
                        $imgNum = (($femaleImg - 1) % 99) + 1;
                        $user->avatar_url = 'https://randomuser.me/api/portraits/women/' . $imgNum . '.jpg';
                        $femaleImg++;
                    } else {
                        $imgNum = (($maleImg - 1) % 99) + 1;
                        $user->avatar_url = 'https://randomuser.me/api/portraits/men/' . $imgNum . '.jpg';
                        $maleImg++;
                    }

                    $user->save();
                    $updated++;
                } catch (\Exception $e) {
                    $errors[] = "User {$user->id}: " . $e->getMessage();
                }
            }
        });

        $output[] = "Updated {$updated} user avatars!";

        if (count($errors) > 0) {
            $output[] = "Errors encountered:";
            foreach (array_slice($errors, 0, 10) as $error) {
                $output[] = "- " . $error;
            }
            if (count($errors) > 10) {
                $output[] = "... and " . (count($errors) - 10) . " more errors";
            }
        }

        $output[] = "";
        $output[] = "You can now visit /contacts to see the avatars.";
        $output[] = "After confirming, remove this route from routes/web.php";

    } catch (\Exception $e) {
        $output[] = "FATAL ERROR: " . $e->getMessage();
        $output[] = "File: " . $e->getFile() . ":" . $e->getLine();
    }

    return "<pre>" . implode("\n", $output) . "</pre>";
});

// Temporary route to reset dashboard - visit once then remove
Route::get('/reset-dashboard-temp', function () {
    if (!auth()->check()) {
        return redirect('/login');
    }

    $user = auth()->user();
    $output = [];
    $output[] = "Resetting dashboard for " . $user->first_name . " " . $user->last_name . "...";

    try {
        // Delete existing dashboards for this user
        $deleted = \App\Models\Dashboard::where('user_id', $user->id)->delete();
        $output[] = "Deleted {$deleted} existing dashboard(s).";

        // Create new default dashboard with updated layout
        $dashboard = \App\Models\Dashboard::createDefault($user);
        $output[] = "Created new dashboard with " . $dashboard->widgets()->count() . " widgets.";

        $output[] = "";
        $output[] = "Done! Visit /dashboard to see the new layout.";
        $output[] = "After confirming, remove this route from routes/web.php";

    } catch (\Exception $e) {
        $output[] = "ERROR: " . $e->getMessage();
    }

    return "<pre>" . implode("\n", $output) . "</pre>";
})->middleware('auth');

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
    // Dashboard (HubSpot-style customizable)
    Route::get('/dashboard', App\Livewire\Dashboard\DashboardIndex::class)->name('dashboard');
    Route::get('/dashboards', App\Livewire\Dashboard\DashboardList::class)->name('dashboards.index');

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

    // Survey Voice Transcription
    Route::post('/api/surveys/transcribe', [App\Http\Controllers\Api\SurveyTranscriptionController::class, 'transcribe'])->name('api.surveys.transcribe');

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

    // Resource Library
    Route::get('/resources', App\Livewire\ResourceLibrary::class)->name('resources.index');
    Route::get('/resources/providers/{provider}', App\Livewire\ProviderProfile::class)->name('resources.providers.show');
    Route::get('/resources/programs/{program}', App\Livewire\ProgramDetail::class)->name('resources.programs.show');
    Route::get('/resources/courses/{course}', App\Livewire\MiniCourseViewer::class)->name('resources.courses.show');
    Route::get('/resources/courses/{course}/edit', App\Livewire\MiniCourseEditor::class)->name('resources.courses.edit');
    Route::get('/resources/courses/create', App\Livewire\MiniCourseEditor::class)->name('resources.courses.create');

    // Mini-Courses API
    Route::prefix('api/mini-courses')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\MiniCourseController::class, 'index'])->name('api.mini-courses.index');
        Route::post('/', [App\Http\Controllers\Api\MiniCourseController::class, 'store'])->name('api.mini-courses.store');
        Route::get('/templates', [App\Http\Controllers\Api\MiniCourseController::class, 'templates'])->name('api.mini-courses.templates');
        Route::post('/generate', [App\Http\Controllers\Api\MiniCourseController::class, 'generate'])->name('api.mini-courses.generate');
        Route::get('/{course}', [App\Http\Controllers\Api\MiniCourseController::class, 'show'])->name('api.mini-courses.show');
        Route::put('/{course}', [App\Http\Controllers\Api\MiniCourseController::class, 'update'])->name('api.mini-courses.update');
        Route::delete('/{course}', [App\Http\Controllers\Api\MiniCourseController::class, 'destroy'])->name('api.mini-courses.destroy');
        Route::post('/{course}/duplicate', [App\Http\Controllers\Api\MiniCourseController::class, 'duplicate'])->name('api.mini-courses.duplicate');
        Route::post('/{course}/publish', [App\Http\Controllers\Api\MiniCourseController::class, 'publish'])->name('api.mini-courses.publish');
        Route::post('/{course}/archive', [App\Http\Controllers\Api\MiniCourseController::class, 'archive'])->name('api.mini-courses.archive');
        Route::post('/{course}/suggest-edits', [App\Http\Controllers\Api\MiniCourseController::class, 'suggestEdits'])->name('api.mini-courses.suggest-edits');

        // Versions
        Route::get('/{course}/versions', [App\Http\Controllers\Api\MiniCourseController::class, 'versions'])->name('api.mini-courses.versions');
        Route::post('/{course}/versions/{version}/restore', [App\Http\Controllers\Api\MiniCourseController::class, 'restoreVersion'])->name('api.mini-courses.versions.restore');

        // Steps
        Route::get('/{course}/steps', [App\Http\Controllers\Api\MiniCourseStepController::class, 'index'])->name('api.mini-course-steps.index');
        Route::post('/{course}/steps', [App\Http\Controllers\Api\MiniCourseStepController::class, 'store'])->name('api.mini-course-steps.store');
        Route::get('/{course}/steps/{step}', [App\Http\Controllers\Api\MiniCourseStepController::class, 'show'])->name('api.mini-course-steps.show');
        Route::put('/{course}/steps/{step}', [App\Http\Controllers\Api\MiniCourseStepController::class, 'update'])->name('api.mini-course-steps.update');
        Route::delete('/{course}/steps/{step}', [App\Http\Controllers\Api\MiniCourseStepController::class, 'destroy'])->name('api.mini-course-steps.destroy');
        Route::put('/{course}/steps/reorder', [App\Http\Controllers\Api\MiniCourseStepController::class, 'reorder'])->name('api.mini-course-steps.reorder');
        Route::post('/{course}/steps/{step}/generate-content', [App\Http\Controllers\Api\MiniCourseStepController::class, 'generateContent'])->name('api.mini-course-steps.generate-content');

        // Enrollments
        Route::post('/{course}/enroll/{student}', [App\Http\Controllers\Api\EnrollmentController::class, 'enroll'])->name('api.mini-courses.enroll');
        Route::get('/{course}/enrollments', [App\Http\Controllers\Api\EnrollmentController::class, 'indexByCourse'])->name('api.mini-courses.enrollments');
    });

    // Enrollments API
    Route::prefix('api/enrollments')->group(function () {
        Route::get('/{enrollment}', [App\Http\Controllers\Api\EnrollmentController::class, 'show'])->name('api.enrollments.show');
        Route::put('/{enrollment}/progress', [App\Http\Controllers\Api\EnrollmentController::class, 'updateProgress'])->name('api.enrollments.progress');
        Route::post('/{enrollment}/step/{step}/complete', [App\Http\Controllers\Api\EnrollmentController::class, 'completeStep'])->name('api.enrollments.complete-step');
        Route::post('/{enrollment}/step/{step}/skip', [App\Http\Controllers\Api\EnrollmentController::class, 'skipStep'])->name('api.enrollments.skip-step');
        Route::post('/{enrollment}/withdraw', [App\Http\Controllers\Api\EnrollmentController::class, 'withdraw'])->name('api.enrollments.withdraw');
        Route::get('/{enrollment}/step-progress', [App\Http\Controllers\Api\EnrollmentController::class, 'stepProgress'])->name('api.enrollments.step-progress');
    });

    // Course Suggestions API
    Route::prefix('api/suggestions')->group(function () {
        Route::get('/{contactType}/{contactId}/courses', [App\Http\Controllers\Api\CourseSuggestionController::class, 'index'])->name('api.suggestions.courses.index');
        Route::post('/courses/generate/{student}', [App\Http\Controllers\Api\CourseSuggestionController::class, 'generate'])->name('api.suggestions.courses.generate');
        Route::post('/courses/{suggestion}/accept', [App\Http\Controllers\Api\CourseSuggestionController::class, 'accept'])->name('api.suggestions.courses.accept');
        Route::post('/courses/{suggestion}/decline', [App\Http\Controllers\Api\CourseSuggestionController::class, 'decline'])->name('api.suggestions.courses.decline');
        Route::post('/triggers/evaluate/{student}', [App\Http\Controllers\Api\CourseSuggestionController::class, 'evaluateTriggers'])->name('api.suggestions.triggers.evaluate');
        Route::get('/providers/{student}', [App\Http\Controllers\Api\CourseSuggestionController::class, 'providerRecommendations'])->name('api.suggestions.providers');
        Route::get('/signals/{student}', [App\Http\Controllers\Api\CourseSuggestionController::class, 'signals'])->name('api.suggestions.signals');
    });

    // Student Enrollments API
    Route::get('/api/students/{student}/enrollments', [App\Http\Controllers\Api\EnrollmentController::class, 'indexByStudent'])->name('api.students.enrollments');

    // Collect (coming soon)
    Route::get('/collect', function () {
        return view('collect.index');
    })->name('collect.index');

    // Distribute (coming soon)
    Route::get('/distribute', function () {
        return view('distribute.index');
    })->name('distribute.index');

    // Marketplace (coming soon)
    Route::get('/marketplace', function () {
        return view('marketplace.index');
    })->name('marketplace.index');

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
