<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DisableScrambleOnAdmin
{
    /**
     * Temporarily disable Scramble API docs on the admin subdomain.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (str_starts_with($request->getHost(), 'admin.')) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return $next($request);
    }
}
