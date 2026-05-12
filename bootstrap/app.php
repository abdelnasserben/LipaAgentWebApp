<?php

use App\Exceptions\ApiException;
use App\Services\Api\Support\ApiErrorMessage;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'agent.auth' => \App\Http\Middleware\AgentAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ApiException $exception, Request $request) {
            $message = ApiErrorMessage::fromException($exception);

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], $exception->statusCode() ?: 500);
            }

            if ($exception->isAuthenticationFailure()) {
                session()->forget([
                    'agent_authenticated',
                    'agent_phone',
                    'agent_access_token',
                    'agent_access_token_expires_at',
                    'agent_refresh_token',
                    'agent_refresh_token_expires_at',
                ]);

                return redirect()->route('login')->with('api_error', $message);
            }

            return redirect()->back()->with('api_error', $message);
        });
    })->create();
