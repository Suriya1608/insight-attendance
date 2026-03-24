<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    protected function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();
        return $user;
    }
}
