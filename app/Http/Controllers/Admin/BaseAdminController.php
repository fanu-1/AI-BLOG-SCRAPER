<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

abstract class BaseAdminController extends Controller
{
    protected function admin(): User
    {
        $user = Auth::user();

        abort_unless($user && $user->is_admin, 403);

        return $user;
    }
}
