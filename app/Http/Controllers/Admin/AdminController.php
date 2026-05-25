<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::all();

        return view('superadmin.dashboard.superadmin', compact('users'));
    }
}
