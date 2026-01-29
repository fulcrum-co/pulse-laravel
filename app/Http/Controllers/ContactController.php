<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        return view('contacts.index');
    }

    public function show(Request $request, Student $student)
    {
        $student->load(['user', 'organization', 'surveyAttempts', 'resourceAssignments.resource']);

        // Get suggested resources based on risk level
        $suggestedResources = \App\Models\Resource::forOrganization($student->org_id)
            ->active()
            ->whereJsonContains('target_risk_levels', $student->risk_level)
            ->limit(5)
            ->get();

        return view('contacts.show', compact('student', 'suggestedResources'));
    }
}
