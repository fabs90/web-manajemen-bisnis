<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class VerifyUserController extends Controller
{
    public function index(){
        $users = User::where('role', '!=', 'superadmin')->get();
        return view('superadmin.dashboard.verify-account.index', compact('users'));
    }
}
