<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/** Admin > Help: plain "how do I..." answers for the shop team. */
class HelpController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Help/Index', [
            'settingsOpen' => (bool) config('shop.settings_access'),
        ]);
    }
}
