<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDatabaseUpdated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $migrator = app('migrator');
        if (array_diff(array_keys($migrator->getMigrationFiles(database_path('migrations'))), $migrator->getRepository()->getRan()) !== []) {
            return redirect()->route('admin.database-update');
        }

        return $next($request);
    }
}
