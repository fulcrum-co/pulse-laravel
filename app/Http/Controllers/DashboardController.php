<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Survey;
use App\Models\SurveyAttempt;
use App\Models\Resource;
use App\Models\ResourceAssignment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $orgId = $user->org_id;

        // Get student metrics
        $studentMetrics = [
            'good' => Student::forOrganization($orgId)->riskLevel('good')->count(),
            'low' => Student::forOrganization($orgId)->riskLevel('low')->count(),
            'high' => Student::forOrganization($orgId)->riskLevel('high')->count(),
            'total' => Student::forOrganization($orgId)->count(),
        ];

        // Get survey metrics
        $surveyMetrics = [
            'active' => Survey::forOrganization($orgId)->active()->count(),
            'completed_this_week' => SurveyAttempt::whereHas('survey', function ($q) use ($orgId) {
                $q->where('org_id', $orgId);
            })->completed()->where('completed_at', '>=', now()->startOfWeek())->count(),
        ];

        // Get suggested resources count
        $suggestedResourcesCount = Resource::forOrganization($orgId)->active()->count();

        // Get resource assignments pending
        $pendingAssignments = ResourceAssignment::whereHas('student', function ($q) use ($orgId) {
            $q->where('org_id', $orgId);
        })->where('status', 'assigned')->count();

        return view('dashboard.index', compact(
            'studentMetrics',
            'surveyMetrics',
            'suggestedResourcesCount',
            'pendingAssignments'
        ));
    }
}
