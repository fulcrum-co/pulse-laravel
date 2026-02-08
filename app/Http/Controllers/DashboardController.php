<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\ResourceAssignment;
use App\Models\Student;
use App\Models\Survey;
use App\Models\SurveyAttempt;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Get all organization IDs the user can access
        $accessibleOrgIds = $user->getAccessibleOrganizations()->pluck('id')->toArray();

        // Use the effective org_id (current_org_id or primary org_id)
        $effectiveOrgId = $user->effective_org_id;

        // For district admins, aggregate data from all accessible organizations
        // For school users, only show their organization's data
        $orgIds = $user->isAdmin() && count($accessibleOrgIds) > 1
            ? $accessibleOrgIds
            : [$effectiveOrgId];

        // Get student metrics across accessible organizations
        $studentMetrics = [
            'good' => Student::whereIn('org_id', $orgIds)->riskLevel('good')->count(),
            'low' => Student::whereIn('org_id', $orgIds)->riskLevel('low')->count(),
            'high' => Student::whereIn('org_id', $orgIds)->riskLevel('high')->count(),
            'total' => Student::whereIn('org_id', $orgIds)->count(),
        ];

        // Get survey metrics across accessible organizations
        $surveyMetrics = [
            'active' => Survey::whereIn('org_id', $orgIds)->active()->count(),
            'completed_this_week' => SurveyAttempt::whereHas('survey', function ($q) use ($orgIds) {
                $q->whereIn('org_id', $orgIds);
            })->completed()->where('completed_at', '>=', now()->startOfWeek())->count(),
        ];

        // Get suggested resources count across accessible organizations
        $suggestedResourcesCount = Resource::whereIn('org_id', $orgIds)->active()->count();

        // Get resource assignments pending across accessible organizations
        $pendingAssignments = ResourceAssignment::whereHas('student', function ($q) use ($orgIds) {
            $q->whereIn('org_id', $orgIds);
        })->where('status', 'assigned')->count();

        return view('dashboard.index', compact(
            'studentMetrics',
            'surveyMetrics',
            'suggestedResourcesCount',
            'pendingAssignments'
        ));
    }
}
