<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */

    protected function redirectTo(Request $request)
    {
        // JSON を期待する API リクエストならリダイレクトさせない
        if ($request->expectsJson() || $request->is('api/*')) {
            return;
        }

        return route('login');
    }
}
