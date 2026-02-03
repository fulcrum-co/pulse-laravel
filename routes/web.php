<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Register broadcast authentication routes
Broadcast::routes(['middleware' => ['web', 'auth']]);
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactMetricController;
use App\Http\Controllers\ContactNoteController;
use App\Http\Controllers\FocusAreaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ObjectiveController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResourceSuggestionController;
use App\Http\Controllers\SurveyController;
use App\Models\UserNotification;

// Notification unsubscribe - signed URL, no auth required
Route::get('/notifications/unsubscribe/{user}', [NotificationController::class, 'unsubscribe'])
    ->name('notifications.unsubscribe')
    ->middleware(['signed', 'throttle:public']);

// Notification resolve API - for task flow
Route::post('/api/notifications/{id}/resolve', function (int $id) {
    $notification = UserNotification::find($id);

    if (! $notification) {
        return response()->json(['error' => 'Notification not found'], 404);
    }

    if ($notification->user_id !== auth()->id()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Resolve if not already resolved (idempotent)
    $wasResolved = false;
    if ($notification->status !== UserNotification::STATUS_RESOLVED) {
        $wasResolved = $notification->resolve();
    }

    // Return updated unread count for header badge
    $unreadCount = UserNotification::getUnreadCountForUser(auth()->id());

    return response()->json([
        'success' => true,
        'resolved' => $wasResolved,
        'unread_count' => $unreadCount,
    ]);
})->middleware(['web', 'auth'])->name('notifications.resolve');

// Root redirect to dashboard
Route::get('/', function () {
    return redirect('/dashboard');
})->name('home');


// Public dashboard view (shareable reports, no auth required)
Route::get('/dashboard/{token}', [ReportController::class, 'publicView'])->name('reports.public');

// Public survey response route (for SMS/email links)
Route::get('/surveys/{survey}/respond/{attempt}', function ($survey, $attempt) {
    // This will be handled by a Livewire component
    return view('surveys.respond', compact('survey', 'attempt'));
})->name('surveys.respond')->middleware('throttle:public');

// Public certificate verification (no auth required)
Route::get('/verify/{uuid}', [App\Http\Controllers\CertificateController::class, 'verify'])->name('certificates.verify');

// Sinch Webhooks (no auth required)
Route::prefix('webhooks/surveys')->group(function () {
    Route::post('/sinch/voice', [App\Http\Controllers\SurveyWebhookController::class, 'handleVoice'])->name('webhooks.surveys.voice')->middleware('throttle:public');
    Route::post('/sinch/sms', [App\Http\Controllers\SurveyWebhookController::class, 'handleSms'])->name('webhooks.surveys.sms')->middleware('throttle:public');
});

// Guest routes (only accessible when not logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth');
});

// Protected routes (only accessible when logged in)
Route::middleware('auth')->group(function () {
    // Demo Role Switcher (simple URL-based, no Livewire)
    Route::get('/demo-role/{role}', [App\Http\Controllers\DemoRoleController::class, 'switch'])->name('demo.role.switch');

    // Dashboard (HubSpot-style customizable)
    Route::get('/dashboard', App\Livewire\Dashboard\DashboardIndex::class)->name('dashboard');
    Route::get('/dashboards', App\Livewire\Dashboard\DashboardList::class)->name('dashboards.index');

    // Participant Experience - Cohort-based Learning
    Route::prefix('learn')->group(function () {
        Route::get('/', App\Livewire\Cohorts\CohortDashboard::class)->name('learn.dashboard');
        Route::get('/cohort/{cohort}', App\Livewire\Cohorts\CohortViewer::class)->name('learn.cohort');
    });

    // Contacts (Participants, Instructors, Direct Supervisors)
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/participants/{participant}', [ContactController::class, 'show'])->name('contacts.show');
    Route::get('/contacts/instructors/{instructor}', [ContactController::class, 'showInstructor'])->name('contacts.instructor');
    Route::get('/contacts/direct_supervisors/{direct_supervisor}', [ContactController::class, 'showDirectSupervisor'])->name('contacts.direct_supervisor');

    // Contact Lists
    Route::get('/contacts/lists', App\Livewire\ContactListManager::class)->name('contacts.lists');

    // Contact Notes API
    Route::get('/api/contacts/{contactType}/{contactId}/notes', [ContactNoteController::class, 'index'])->name('api.notes.index');
    Route::post('/api/contacts/notes', [ContactNoteController::class, 'store'])->name('api.notes.store');
    Route::put('/api/contacts/notes/{note}', [ContactNoteController::class, 'update'])->name('api.notes.update');
    Route::delete('/api/contacts/notes/{note}', [ContactNoteController::class, 'destroy'])->name('api.notes.destroy');
    Route::get('/api/contacts/notes/{note}/audio', [ContactNoteController::class, 'audio'])->name('api.notes.audio');

    // Contact Metrics API
    Route::post('/api/metrics/time-series', [ContactMetricController::class, 'timeSeries'])->name('api.metrics.timeSeries');
    Route::post('/api/metrics', [ContactMetricController::class, 'store'])->name('api.metrics.store');
    Route::get('/api/metrics/heat-map/{participant}', [ContactMetricController::class, 'heatMap'])->name('api.metrics.heatMap');
    Route::get('/api/metrics/available', [ContactMetricController::class, 'available'])->name('api.metrics.available');

    // Resource Suggestions API
    Route::get('/api/suggestions/{contactType}/{contactId}', [ResourceSuggestionController::class, 'index'])->name('api.suggestions.index');
    Route::post('/api/suggestions', [ResourceSuggestionController::class, 'store'])->name('api.suggestions.store');
    Route::put('/api/suggestions/{suggestion}/review', [ResourceSuggestionController::class, 'review'])->name('api.suggestions.review');
    Route::post('/api/suggestions/generate/{participant}', [ResourceSuggestionController::class, 'generate'])->name('api.suggestions.generate');

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
    Route::post('/surveys/{survey}/push', [SurveyController::class, 'push'])->name('surveys.push');

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

    // Plans
    Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
    Route::get('/plans/create', [PlanController::class, 'create'])->name('plans.create');
    Route::post('/plans', [PlanController::class, 'store'])->name('plans.store');
    Route::get('/plans/{plan}', [PlanController::class, 'show'])->name('plans.show');
    Route::get('/plans/{plan}/edit', [PlanController::class, 'edit'])->name('plans.edit');
    Route::put('/plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
    Route::delete('/plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');
    Route::post('/plans/{plan}/duplicate', [PlanController::class, 'duplicate'])->name('plans.duplicate');
    Route::post('/plans/{plan}/push', [PlanController::class, 'push'])->name('plans.push');

    // Focus Areas
    Route::post('/plans/{plan}/focus-areas', [FocusAreaController::class, 'store'])->name('focus-areas.store');
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

    // Goals (OKR-style)
    Route::post('/plans/{plan}/goals', [App\Http\Controllers\GoalController::class, 'store'])->name('goals.store');
    Route::put('/goals/{goal}', [App\Http\Controllers\GoalController::class, 'update'])->name('goals.update');
    Route::delete('/goals/{goal}', [App\Http\Controllers\GoalController::class, 'destroy'])->name('goals.destroy');
    Route::put('/goals/reorder', [App\Http\Controllers\GoalController::class, 'reorder'])->name('goals.reorder');

    // Key Results
    Route::post('/goals/{goal}/key-results', [App\Http\Controllers\KeyResultController::class, 'store'])->name('key-results.store');
    Route::put('/key-results/{keyResult}', [App\Http\Controllers\KeyResultController::class, 'update'])->name('key-results.update');
    Route::delete('/key-results/{keyResult}', [App\Http\Controllers\KeyResultController::class, 'destroy'])->name('key-results.destroy');

    // Milestones
    Route::post('/plans/{plan}/milestones', [App\Http\Controllers\MilestoneController::class, 'store'])->name('milestones.store');
    Route::put('/milestones/{milestone}', [App\Http\Controllers\MilestoneController::class, 'update'])->name('milestones.update');
    Route::delete('/milestones/{milestone}', [App\Http\Controllers\MilestoneController::class, 'destroy'])->name('milestones.destroy');
    Route::post('/milestones/{milestone}/complete', [App\Http\Controllers\MilestoneController::class, 'complete'])->name('milestones.complete');

    // Progress Updates
    Route::get('/plans/{plan}/progress', [App\Http\Controllers\ProgressUpdateController::class, 'index'])->name('progress.index');
    Route::post('/plans/{plan}/progress', [App\Http\Controllers\ProgressUpdateController::class, 'store'])->name('progress.store');
    Route::post('/plans/{plan}/progress/generate-summary', [App\Http\Controllers\ProgressUpdateController::class, 'generateSummary'])->name('progress.generate-summary');
    Route::get('/plans/{plan}/progress/analytics', [App\Http\Controllers\ProgressUpdateController::class, 'analytics'])->name('progress.analytics');

    // Resource Library - Hub + Sub-pages
    Route::get('/resources', App\Livewire\ResourceHub::class)->name('resources.index');

    // Content Library (sub-page)
    Route::get('/resources/content', App\Livewire\ContentLibrary::class)->name('resources.content.index');

    // Providers Directory (sub-page)
    Route::get('/resources/providers', App\Livewire\ProviderDirectory::class)->name('resources.providers.index');
    Route::get('/resources/providers/{provider}', App\Livewire\ProviderProfile::class)->name('resources.providers.show');

    // Programs Catalog (sub-page)
    Route::get('/resources/programs', App\Livewire\ProgramCatalog::class)->name('resources.programs.index');
    Route::get('/resources/programs/{program}', App\Livewire\ProgramDetail::class)->name('resources.programs.show');

    // Courses / Learning Center (sub-page)
    Route::get('/resources/courses', App\Livewire\LearningCenter::class)->name('resources.courses.index');
    Route::get('/resources/courses/create', App\Livewire\MiniCourseEditor::class)->name('resources.courses.create');
    Route::get('/resources/courses/{course}', App\Livewire\MiniCourseViewer::class)->name('resources.courses.show');
    Route::get('/resources/courses/{course}/edit', App\Livewire\MiniCourseEditor::class)->name('resources.courses.edit');

    // Individual Resource Detail
    Route::get('/resources/{resource}', App\Livewire\ResourceDetail::class)->name('resources.show');

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
        Route::post('/{course}/enroll/{participant}', [App\Http\Controllers\Api\EnrollmentController::class, 'enroll'])->name('api.mini-courses.enroll');
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
        Route::post('/courses/generate/{participant}', [App\Http\Controllers\Api\CourseSuggestionController::class, 'generate'])->name('api.suggestions.courses.generate');
        Route::post('/courses/{suggestion}/accept', [App\Http\Controllers\Api\CourseSuggestionController::class, 'accept'])->name('api.suggestions.courses.accept');
        Route::post('/courses/{suggestion}/decline', [App\Http\Controllers\Api\CourseSuggestionController::class, 'decline'])->name('api.suggestions.courses.decline');
        Route::post('/triggers/evaluate/{participant}', [App\Http\Controllers\Api\CourseSuggestionController::class, 'evaluateTriggers'])->name('api.suggestions.triggers.evaluate');
        Route::get('/providers/{participant}', [App\Http\Controllers\Api\CourseSuggestionController::class, 'providerRecommendations'])->name('api.suggestions.providers');
        Route::get('/signals/{participant}', [App\Http\Controllers\Api\CourseSuggestionController::class, 'signals'])->name('api.suggestions.signals');
    });

    // Participant Enrollments API
    Route::get('/api/participants/{participant}/enrollments', [App\Http\Controllers\Api\EnrollmentController::class, 'indexByLearner'])->name('api.participants.enrollments');

    // Collect
    Route::get('/collect', App\Livewire\Collect\CollectionList::class)->name('collect.index');
    Route::get('/collect/create', App\Livewire\Collect\CollectionCreator::class)->name('collect.create');
    Route::get('/collect/{collection}', function (\App\Models\Collection $collection) {
        // Placeholder for collection detail view (Phase 4)
        return redirect()->route('collect.index');
    })->name('collect.show');

    // Distribute
    Route::get('/distribute', App\Livewire\Distribute\DistributeList::class)->name('distribute.index');
    Route::get('/distribute/create', App\Livewire\Distribute\DistributionCreator::class)->name('distribute.create');
    Route::get('/distribute/{distribution}', App\Livewire\Distribute\DistributionDetail::class)->name('distribute.show');
    Route::get('/distribute/{distribution}/edit', App\Livewire\Distribute\DistributionCreator::class)->name('distribute.edit');

    // Marketplace
    Route::prefix('marketplace')->group(function () {
        // Hub
        Route::get('/', App\Livewire\Marketplace\MarketplaceHub::class)->name('marketplace.index');

        // Category pages
        Route::get('/surveys', App\Livewire\Marketplace\MarketplaceSurveys::class)->name('marketplace.surveys');
        Route::get('/strategies', App\Livewire\Marketplace\MarketplaceStrategies::class)->name('marketplace.strategies');
        Route::get('/content', App\Livewire\Marketplace\MarketplaceContent::class)->name('marketplace.content');
        Route::get('/providers', App\Livewire\Marketplace\MarketplaceProviders::class)->name('marketplace.providers');

        // Item detail
        Route::get('/item/{uuid}', App\Livewire\Marketplace\MarketplaceItemDetail::class)->name('marketplace.item');

        // Seller public profile (to be implemented in Phase 3)
        Route::get('/sellers/{slug}', function ($slug) {
            return redirect()->route('marketplace.index');
        })->name('marketplace.sellers.show');

        // Buyer's purchases (to be implemented in Phase 5)
        Route::get('/my-purchases', function () {
            return redirect()->route('marketplace.index');
        })->name('marketplace.purchases');

        // Seller routes
        Route::prefix('seller')->group(function () {
            Route::get('/create', App\Livewire\Marketplace\SellerProfileCreate::class)->name('marketplace.seller.create');
            Route::get('/dashboard', App\Livewire\Marketplace\SellerDashboard::class)->name('marketplace.seller.dashboard');

            // Seller items management (to be implemented in Phase 4)
            Route::get('/items', function () {
                return redirect()->route('marketplace.seller.dashboard');
            })->name('marketplace.seller.items');
            Route::get('/items/create', function () {
                return redirect()->route('marketplace.seller.dashboard');
            })->name('marketplace.seller.items.create');
            Route::get('/items/{item}/edit', function ($item) {
                return redirect()->route('marketplace.seller.dashboard');
            })->name('marketplace.seller.items.edit');

            // Seller analytics (to be implemented in Phase 7)
            Route::get('/analytics', function () {
                return redirect()->route('marketplace.seller.dashboard');
            })->name('marketplace.seller.analytics');

            // Seller reviews (to be implemented in Phase 6)
            Route::get('/reviews', function () {
                return redirect()->route('marketplace.seller.dashboard');
            })->name('marketplace.seller.reviews');

            // Seller payouts (to be implemented in Phase 5)
            Route::get('/payouts', function () {
                return redirect()->route('marketplace.seller.dashboard');
            })->name('marketplace.seller.payouts');
        });
    });

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::get('/reports/{report}/edit', [ReportController::class, 'edit'])->name('reports.edit');
    Route::get('/reports/{report}/preview', [ReportController::class, 'preview'])->name('reports.preview');
    Route::post('/reports/{report}/duplicate', [ReportController::class, 'duplicate'])->name('reports.duplicate');
    Route::post('/reports/{report}/push', [ReportController::class, 'push'])->name('reports.push');
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

    // Provider Chat & Messaging
    Route::prefix('messages')->group(function () {
        Route::get('/', App\Livewire\Chat\ProviderChatList::class)->name('messages.index');
        Route::get('/{conversation}', App\Livewire\Chat\ProviderChatList::class)->name('messages.show');
    });

    // Admin Settings
    Route::prefix('admin')->group(function () {
        Route::get('/settings/ai-courses', App\Livewire\Admin\AICourseSettings::class)->name('admin.settings.ai-courses');
        Route::get('/settings/terminology', App\Livewire\Admin\TerminologySettings::class)->name('admin.settings.terminology');

        // Cohort Management
        Route::get('/cohorts', App\Livewire\Cohorts\CohortManager::class)->name('admin.cohorts.index');
        Route::get('/cohorts/{cohort}', App\Livewire\Cohorts\CohortDetail::class)->name('admin.cohorts.show');
        Route::get('/cohorts/{cohort}/enroll', App\Livewire\Cohorts\CohortEnrollment::class)->name('admin.cohorts.enroll');
        Route::get('/cohorts/{cohort}/schedule', App\Livewire\Cohorts\DripScheduleBuilder::class)->name('admin.cohorts.schedule');

        // Moderation
        Route::get('/moderation', App\Livewire\Admin\ModerationQueue::class)->name('admin.moderation');
        Route::get('/moderation/task-flow', App\Livewire\Admin\ModerationTaskFlow::class)->name('admin.moderation.task-flow');
        Route::get('/moderation/dashboard', App\Livewire\Admin\ModerationDashboard::class)->name('admin.moderation.dashboard');
        Route::get('/moderation/{result}/edit', App\Livewire\Admin\ModerationEdit::class)->name('admin.moderation.edit');

        // Help Center Admin - Tooltips only (other Help Center components to be added later)
        Route::get('/help', App\Livewire\Admin\HelpHintManager::class)->name('admin.help');
        Route::get('/help/hints', App\Livewire\Admin\HelpHintManager::class)->name('admin.help-hints');
    });

    // Help Center API routes
    Route::prefix('api/help')->group(function () {
        Route::get('/page-hints', [App\Http\Controllers\Api\PageHelpController::class, 'allHints']);
        Route::get('/page-hints/{context}', [App\Http\Controllers\Api\PageHelpController::class, 'pageHints']);

        // CRUD for visual editor (admin only)
        Route::post('/hints', [App\Http\Controllers\Api\PageHelpController::class, 'store']);
        Route::put('/hints/{id}', [App\Http\Controllers\Api\PageHelpController::class, 'update']);
        Route::delete('/hints/{id}', [App\Http\Controllers\Api\PageHelpController::class, 'destroy']);
        Route::post('/hints/batch-update', [App\Http\Controllers\Api\PageHelpController::class, 'batchUpdate']);
    });

    // Public Help Center (placeholder - redirect to admin for now)
    Route::get('/help', function () {
        return redirect()->route('admin.help');
    })->name('help.index');

    // Settings (placeholder)
    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings.index');

    // Help Center
    Route::prefix('help')->name('help.')->group(function () {
        Route::get('/', App\Livewire\Help\KnowledgeBase::class)->name('index');
        Route::get('/search', App\Livewire\Help\SearchResults::class)->name('search');
        Route::get('/category/{slug}', App\Livewire\Help\CategoryBrowser::class)->name('category');
        Route::get('/article/{slug}', App\Livewire\Help\ArticleViewer::class)->name('article');
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Support Tickets API (works for both authenticated and guest users)
Route::post('/api/support-tickets', [App\Http\Controllers\Api\SupportTicketController::class, 'store'])
    ->middleware('web')
    ->name('api.support-tickets.store');

// Help Widget API (authenticated users)
Route::prefix('api/help')->middleware(['web', 'auth'])->group(function () {
    Route::get('/featured-articles', [App\Http\Controllers\Api\HelpController::class, 'featuredArticles']);
    Route::get('/categories', [App\Http\Controllers\Api\HelpController::class, 'categories']);
    Route::get('/search', [App\Http\Controllers\Api\HelpController::class, 'search']);
    Route::get('/page-hints', [App\Http\Controllers\Api\PageHelpController::class, 'allHints']);
    Route::get('/page-hints/{context}', [App\Http\Controllers\Api\PageHelpController::class, 'pageHints']);

    // Visual Editor CRUD routes (admin only)
    Route::post('/hints', [App\Http\Controllers\Api\PageHelpController::class, 'store']);
    Route::put('/hints/{id}', [App\Http\Controllers\Api\PageHelpController::class, 'update']);
    Route::delete('/hints/{id}', [App\Http\Controllers\Api\PageHelpController::class, 'destroy']);
    Route::post('/hints/batch-update', [App\Http\Controllers\Api\PageHelpController::class, 'batchUpdate']);
});

// Admin Routes (requires admin role)
Route::prefix('admin')->middleware(['web', 'auth'])->name('admin.')->group(function () {
    // Help Center Admin
    Route::get('/help', App\Livewire\Admin\HelpAdminDashboard::class)->name('help');
    Route::get('/help/articles', App\Livewire\Admin\HelpArticleManager::class)->name('help-articles');
    Route::get('/help/categories', App\Livewire\Admin\HelpCategoryManager::class)->name('help-categories');
    Route::get('/help/hints', App\Livewire\Admin\HelpHintManager::class)->name('help-hints');
});

// Public Certificate Verification (no auth required)
Route::get('/verify/{uuid}', [App\Http\Controllers\CertificateController::class, 'verify'])->name('certificates.verify');

// Authenticated Certificate Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/certificates', [App\Http\Controllers\CertificateController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/{uuid}', [App\Http\Controllers\CertificateController::class, 'show'])->name('certificates.show');
    Route::get('/certificates/{uuid}/download', [App\Http\Controllers\CertificateController::class, 'download'])->name('certificates.download');
    Route::get('/certificates/{uuid}/linkedin', [App\Http\Controllers\CertificateController::class, 'linkedinShare'])->name('certificates.linkedin');
});
