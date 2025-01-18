<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use app\Models\User;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$subscription): Response
    {
        if ($request->user() && $request->user()->hasAnySubscription($subscription)) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Unauthorized'
        ], 401);
    }

}
