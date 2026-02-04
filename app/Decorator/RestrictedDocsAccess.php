<?php

namespace App\Decorator;

use Illuminate\Support\Facades\Gate;

class RestrictedDocsAccess
{
    public function handle($request, \Closure $next)
    {
        if (app()->environment('local')) {
            return $next($request);
        }

        if (Gate::allows('viewApiDocs')) {
            return $next($request);
        }

        abort(403);
    }
}
