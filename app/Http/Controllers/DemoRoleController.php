<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DemoRoleController extends Controller
{
    public function switch(Request $request, string $role)
    {
        $validRoles = ['actual', 'consultant', 'administrative_role', 'organization_admin', 'support_person', 'instructor', 'participant', 'direct_supervisor'];

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
