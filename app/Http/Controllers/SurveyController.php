<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $surveys = Survey::forOrganization($user->org_id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('surveys.index', compact('surveys'));
    }
}
