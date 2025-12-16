<?php

declare(strict_types=1);

namespace App\Features\Auth\Controllers;

use App\Features\Auth\Actions\EmailVerifyAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function __construct(protected EmailVerifyAction $email_verify_action) {}

    public function __invoke(Request $request): View
    {
        $this->email_verify_action->handle($request->route('id') ?? '');

        return view('email-verified');
    }
}
