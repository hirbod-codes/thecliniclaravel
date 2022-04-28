<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PhonenumberVerified
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
        if ($this->isVerified()) {
            return $next($request);
        }

        if (Str::startsWith($request->path(), 'api')) {
            return response(trans_choice('auth.phonenumber_not_verification', 0), 403);
        } else {
            return redirect('/', 403)->with(trans_choice('auth.phonenumber_not_verification', 0));
        }
    }

    public function isVerified(): bool
    {
        if (($user = Auth::guard('api')->user()) !== null) {
            return $user->phonenumber_verified_at !== null;
        } elseif (($user = Auth::guard('web')->user()) !== null) {
            return $user->phonenumber_verified_at !== null;
        } else {
            return false;
        }
    }
}
