<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditorController extends Controller
{
    public function index(Request $request): View
    {
        $request->session()->forget('active_audit_context');

        return view('auditor');
    }
}
