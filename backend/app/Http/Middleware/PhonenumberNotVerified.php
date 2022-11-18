<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PhonenumberNotVerified
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
        if (!(new PhonenumberVerified)->isVerified()) {
            return $next($request);
        }

        if (Str::startsWith($request->path(), 'api')) {
            return response(trans_choice('auth.phonenumber_already_verification', 0), 403);
        } else {
            return redirect('/', 403)->with(trans_choice('auth.phonenumber_already_verification', 0));
        }
    }
}
