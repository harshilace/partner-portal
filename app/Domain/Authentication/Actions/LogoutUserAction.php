<?php

namespace App\Domain\Authentication\Actions;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Authentication\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutUserAction
{
    public function __construct(
        protected AuditLogger $auditLogger
    ) {}

    /**
     * Terminate the user session and log the security audit event.
     */
    public function execute(?Request $request = null): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user) {
            $this->auditLogger->logLogout($user, $request);
        }

        Auth::guard('web')->logout();

        if ($request?->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }
}
