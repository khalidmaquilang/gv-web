<?php

declare(strict_types=1);

namespace App\Features\Auth\Controllers;

use App\Features\Auth\Actions\ChangePasswordAction;
use App\Features\Auth\Data\ChangePasswordData;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ChangePasswordController extends Controller
{
    public function __construct(protected ChangePasswordAction $change_password_action) {}

    public function __invoke(ChangePasswordData $request): View
    {
        $this->change_password_action->handle($request);

        return view('success-password');
    }
}
