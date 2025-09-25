<?php

namespace MorningMedley\Route\Middleware;

use Closure;

class AuthMiddleware
{
    public function handle($request, Closure $next, string $capability = '')
    {
        if (! \is_user_logged_in()) {
            $this->handleUnauthenticatedUser($request);
        }

        if (! empty($capability) && ! \current_user_can($capability)) {
            $this->handleUnauthenticatedUser($request);
        }

        return $next($request);
    }

    protected function handleUnauthenticatedUser($request)
    {
        \do_action(static::class . "::handleUnauthenticatedUser", $request);
        \wp_die(__("You are not allowed to view this resource.", 'morningmedley'));
    }
}
