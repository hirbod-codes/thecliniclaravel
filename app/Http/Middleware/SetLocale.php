<?php

namespace App\Http\Middleware;

use App\Http\Requests\UpdateLocaleRequest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class SetLocale
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
        $locale = explode('/', $request->path())[1];

        $validator = Validator::make(['locale' => $locale], (new UpdateLocaleRequest)->rules());
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $validated = $validator->validated();

        App::setLocale($validated['locale']);

        return $next($request);
    }
}
