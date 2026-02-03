<?php

namespace App\Providers;

use App\Events\SurveyCompleted;
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
use App\Services\TerminologyService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register TerminologyService as a singleton
        $this->app->singleton(TerminologyService::class, function ($app) {
            return new TerminologyService();
        });
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

        // Register notification observers
        $this->registerNotificationObservers();

        // Register terminology Blade directives
        $this->registerTerminologyDirectives();

        // Register rate limiters
        $this->registerRateLimiters();
    }

    /**
     * Register Blade directives for terminology.
     */
    protected function registerTerminologyDirectives(): void
    {
        // @term('key') - Get terminology for current org
        // @term('key', $orgId) - Get terminology for specific org
        Blade::directive('term', function ($expression) {
            return "<?php echo app(\App\Services\TerminologyService::class)->get({$expression}); ?>";
        });

        // @termPlural('key') - Get plural form
        Blade::directive('termPlural', function ($expression) {
            return "<?php echo app(\App\Services\TerminologyService::class)->plural({$expression}); ?>";
        });
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

    /**
     * Register rate limiters for public/auth endpoints.
     */
    protected function registerRateLimiters(): void
    {
        RateLimiter::for('auth', function (Request $request) {
            $email = (string) $request->input('email');

            return Limit::perMinute(10)->by($email.$request->ip());
        });

        RateLimiter::for('public', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
    }
}
