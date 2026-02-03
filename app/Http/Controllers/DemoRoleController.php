<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DemoRoleController extends Controller
{
    public function switch(Request $request, string $role)
    {
        $validRoles = ['actual', 'consultant', 'superintendent', 'organization_admin', 'counselor', 'teacher', 'learner', 'parent'];

        if (! in_array($role, $validRoles)) {
            abort(404);
        }

        if ($role === 'actual') {
            session()->forget('demo_role_override');
        } else {
            session()->put('demo_role_override', $role);
        }

        return redirect()->back();
    }
}
