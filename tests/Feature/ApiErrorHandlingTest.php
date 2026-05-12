<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\Api\AgentApi;
use App\Contracts\Api\TransactionApi;
use App\Exceptions\ApiException;
use App\Livewire\Agent\Dashboard;
use App\Services\Api\Http\HttpAgentApi;
use App\Services\Api\Http\KomopayClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiErrorHandlingTest extends TestCase
{
    public function test_protected_http_services_use_spec_paths_and_session_bearer_token(): void
    {
        config(['komopay.base_url' => 'https://api.lipa.test']);
        session(['agent_access_token' => 'jwt-access']);

        Http::fake([
            'https://api.lipa.test/api/v1/agent/me' => Http::response([
                'data' => [
                    'fullName' => 'Said Djoumoi',
                    'externalRef' => 'AGENT-00001',
                ],
            ]),
        ]);

        $profile = (new HttpAgentApi(app(KomopayClient::class)))->getProfile();

        $this->assertSame('Said Djoumoi', $profile['fullName']);

        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.lipa.test/api/v1/agent/me'
            && (($request->header('Authorization')[0] ?? null) === 'Bearer jwt-access'));
    }

    public function test_http_client_preserves_raw_security_error_payloads(): void
    {
        config(['komopay.base_url' => 'https://api.lipa.test']);

        Http::fake([
            'https://api.lipa.test/api/v1/agent/me' => Http::response([
                'code' => 'TOKEN_EXPIRED',
                'message' => 'Token expired',
            ], 401),
        ]);

        try {
            (new HttpAgentApi(app(KomopayClient::class)))->getProfile();
        } catch (ApiException $exception) {
            $this->assertSame('TOKEN_EXPIRED', $exception->apiCode());
            $this->assertSame(401, $exception->statusCode());
            $this->assertSame('Token expired', $exception->getMessage());

            return;
        }

        $this->fail('Expected ApiException for raw security error payload.');
    }

    public function test_protected_401_without_error_code_is_treated_as_expired_session(): void
    {
        config(['komopay.base_url' => 'https://api.lipa.test']);

        Http::fake([
            'https://api.lipa.test/api/v1/agent/me' => Http::response([], 401),
        ]);

        try {
            (new HttpAgentApi(app(KomopayClient::class)))->getProfile();
        } catch (ApiException $exception) {
            $this->assertSame('UNAUTHORIZED', $exception->apiCode());
            $this->assertTrue($exception->isAuthenticationFailure());

            return;
        }

        $this->fail('Expected ApiException for unauthorized protected request.');
    }

    public function test_dashboard_displays_api_errors_without_throwing_laravel_debug_page(): void
    {
        $agentApi = new class implements AgentApi
        {
            public function getProfile(): array
            {
                throw new ApiException('WALLET_NOT_FOUND', 404);
            }

            public function getBalance(): array
            {
                return [];
            }

            public function getDailySummary(): array
            {
                return [];
            }
        };

        $transactionApi = new class implements TransactionApi
        {
            public function getTransactions(array $filters = []): array
            {
                return ['data' => [], 'pagination' => []];
            }

            public function getTransaction(string $id): ?array
            {
                return null;
            }

            public function getStatements(array $filters = []): array
            {
                return ['data' => [], 'pagination' => []];
            }
        };

        $component = new Dashboard();
        $component->mount($agentApi, $transactionApi);

        $this->assertSame('Wallet introuvable.', $component->apiError);
    }
}
