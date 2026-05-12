<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Exceptions\ApiException;
use App\Services\Api\Support\ApiErrorMessage;

trait HandlesApiErrors
{
    protected function clearApiError(?string $property = 'apiError'): void
    {
        if ($property !== null && property_exists($this, $property)) {
            $this->{$property} = null;
        }
    }

    protected function showApiError(ApiException $exception, string $property = 'apiError'): void
    {
        $message = ApiErrorMessage::fromException($exception);

        if ($exception->isAuthenticationFailure()) {
            session()->forget([
                'agent_authenticated',
                'agent_phone',
                'agent_access_token',
                'agent_access_token_expires_at',
                'agent_refresh_token',
                'agent_refresh_token_expires_at',
            ]);
            session()->flash('api_error', $message);

            $this->redirect(route('login'), navigate: true);

            return;
        }

        if (property_exists($this, $property)) {
            $this->{$property} = $message;
        }
    }
}
