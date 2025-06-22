<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // aqui você pode passar dados para a dashboard se precisar
        return view('admin.dashboard');
    }
}
