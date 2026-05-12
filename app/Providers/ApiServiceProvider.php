<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Api\AgentApi;
use App\Contracts\Api\AgentAuthApi;
use App\Contracts\Api\CardApi;
use App\Contracts\Api\CommissionApi;
use App\Contracts\Api\EnrollApi;
use App\Contracts\Api\LimitsApi;
use App\Contracts\Api\OperationsApi;
use App\Contracts\Api\TotpApi;
use App\Contracts\Api\TransactionApi;
use App\Services\Api\Http\HttpAgentApi;
use App\Services\Api\Http\HttpAgentAuthApi;
use App\Services\Api\Http\HttpCardApi;
use App\Services\Api\Http\HttpCommissionApi;
use App\Services\Api\Http\HttpEnrollApi;
use App\Services\Api\Http\HttpLimitsApi;
use App\Services\Api\Http\HttpOperationsApi;
use App\Services\Api\Http\HttpTotpApi;
use App\Services\Api\Http\HttpTransactionApi;
use App\Services\Api\Http\KomopayClient;
use App\Services\Api\Mock\MockAgentApi;
use App\Services\Api\Mock\MockAgentAuthApi;
use App\Services\Api\Mock\MockCardApi;
use App\Services\Api\Mock\MockCommissionApi;
use App\Services\Api\Mock\MockEnrollApi;
use App\Services\Api\Mock\MockLimitsApi;
use App\Services\Api\Mock\MockOperationsApi;
use App\Services\Api\Mock\MockTotpApi;
use App\Services\Api\Mock\MockTransactionApi;
use Illuminate\Support\ServiceProvider;

/**
 * Binds every Komopay API contract to either a Mock or HTTP implementation
 * based on `config('komopay.use_mock')` (env: KOMOPAY_USE_MOCK_API).
 *
 * Livewire components and any other consumers should depend only on the
 * contract types — they never touch the mock or http classes directly.
 */
class ApiServiceProvider extends ServiceProvider
{
    /** @var array<class-string, array{0: class-string, 1: class-string}> */
    private const BINDINGS = [
        AgentAuthApi::class   => [MockAgentAuthApi::class,   HttpAgentAuthApi::class],
        AgentApi::class       => [MockAgentApi::class,       HttpAgentApi::class],
        TransactionApi::class => [MockTransactionApi::class, HttpTransactionApi::class],
        CardApi::class        => [MockCardApi::class,        HttpCardApi::class],
        CommissionApi::class  => [MockCommissionApi::class,  HttpCommissionApi::class],
        EnrollApi::class      => [MockEnrollApi::class,      HttpEnrollApi::class],
        LimitsApi::class      => [MockLimitsApi::class,      HttpLimitsApi::class],
        OperationsApi::class  => [MockOperationsApi::class,  HttpOperationsApi::class],
        TotpApi::class        => [MockTotpApi::class,        HttpTotpApi::class],
    ];

    public function register(): void
    {
        $this->app->singleton(KomopayClient::class);

        $useMock = (bool) config('komopay.use_mock', true);

        foreach (self::BINDINGS as $contract => [$mock, $http]) {
            $this->app->bind($contract, $useMock ? $mock : $http);
        }
    }
}
