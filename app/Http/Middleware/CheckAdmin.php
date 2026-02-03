<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        // Check if user is admin type and has any admin role
        if (!$user->isAdmin() && !$user->hasAnyRole(['Super Admin', 'Admin', 'Content Manager', 'Support'])) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        if (!$user->isActive()) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account has been deactivated.');
        }

        return $next($request);
    }
}
