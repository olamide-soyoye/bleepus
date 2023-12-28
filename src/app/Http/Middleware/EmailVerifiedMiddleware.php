<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerifiedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated and email is not verified
        if (Auth::check() && Auth::user()->email_verified_at == null) { 
            return response()->json([
                'message'=>'error',
                'data'=>"Email not verified!",
                'resend_otp' => true,
            ], 403);
        }
        //If user is not verified on /auth/login and does not exist in db
        if (!Auth::check() && User::where('email', $request->email)->first() == null) { 
            return response()->json([
                'message'=>'error',
                'data'=>"Email or Password does not match with our record.",
            ], 403);
        }
        //If user is not verified on /auth/login
        if (!Auth::check() && User::where('email', $request->email)->first()->email_verified_at == null) { 
            return response()->json([
                'message'=>'error',
                'data'=>"Email not verified!",
                'resend_otp' => true,
            ], 403);
        }

       
        return $next($request);
    }
}
