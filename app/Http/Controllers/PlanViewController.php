<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlanViewController extends Controller
{
    public function show(Request $request, Plan $plan)
    {
        return view('plans.signed-view', [
            'plan' => $plan,
        ]);
    }
}
