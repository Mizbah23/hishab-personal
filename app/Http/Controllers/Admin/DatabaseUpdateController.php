<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DatabaseUpdateController extends Controller
{
    public function index(): View
    {
        return view('admin.database-update');
    }

    public function store(): RedirectResponse
    {
        try {
            $result = Cache::store('file')->lock('database-update', 300)->get(fn (): int => Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]));
            if ($result !== 0) {
                return back()->withErrors(['update' => 'Update is busy or could not finish. Please retry shortly.']);
            }
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['update' => 'Update failed. Please contact support or retry.']);
        }

        return redirect()->route('admin.dashboard')->with('status', 'Database updated successfully.');
    }
}
