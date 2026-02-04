<?php

namespace App\Providers;

use App\Events\StrategyDriftDetected;
use App\Events\SurveyCompleted;
use App\Listeners\NotifyStrategyDrift;
use App\Listeners\SurveyCompletedListener;
use App\Models\Activity;
use App\Models\CourseGenerationRequest;
use App\Models\CustomReport;
use App\Models\Objective;
use App\Models\Survey;
use App\Models\WorkflowExecution;
use App\Observers\ActivityObserver;
use App\Observers\CourseGenerationRequestObserver;
use App\Observers\CustomReportObserver;
use App\Observers\ObjectiveObserver;
use App\Observers\SurveyObserver;
use App\Observers\WorkflowExecutionObserver;
use App\Models\PendingExtraction;
use App\Policies\PendingExtractionPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register event listeners
        Event::listen(
            SurveyCompleted::class,
            SurveyCompletedListener::class
        );

        Event::listen(
            StrategyDriftDetected::class,
            NotifyStrategyDrift::class
        );

        Gate::policy(PendingExtraction::class, PendingExtractionPolicy::class);

        // Register notification observers
        $this->registerNotificationObservers();
    }

    /**
     * Register observers for notification generation.
     */
    protected function registerNotificationObservers(): void
    {
        // Workflow execution notifications (completion/failure)
        WorkflowExecution::observe(WorkflowExecutionObserver::class);

        // Survey notifications (assignment, completion)
        Survey::observe(SurveyObserver::class);

        // Strategy notifications (status changes)
        Activity::observe(ActivityObserver::class);
        Objective::observe(ObjectiveObserver::class);

        // Course generation notifications (approval workflow)
        CourseGenerationRequest::observe(CourseGenerationRequestObserver::class);

        // Report notifications (published, assigned)
        CustomReport::observe(CustomReportObserver::class);
    }
}
