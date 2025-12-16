<?php

declare(strict_types=1);

namespace App\Features\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ChangePasswordPageController extends Controller
{
    public function __invoke(): View
    {
        return view('change-password');
    }
}
