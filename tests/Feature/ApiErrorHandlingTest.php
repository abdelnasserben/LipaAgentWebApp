<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\Api\AgentApi;
use App\Contracts\Api\TransactionApi;
use App\Exceptions\ApiException;
use App\Livewire\Agent\Dashboard;
use App\Services\Api\Http\HttpAgentApi;
use App\Services\Api\Http\HttpEnrollApi;
use App\Services\Api\Http\HttpLimitsApi;
use App\Services\Api\Http\HttpOperationsApi;
use App\Services\Api\Http\KomopayClient;
use Illuminate\Http\UploadedFile;
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

    public function test_enroll_payload_only_contains_spec_request_fields(): void
    {
        config(['komopay.base_url' => 'https://api.lipa.test']);
        session(['agent_access_token' => 'jwt-access']);

        Http::fake([
            'https://api.lipa.test/api/v1/agent/customers/enroll' => Http::response([
                'data' => [
                    'customerId' => 'cust-uuid',
                    'externalRef' => 'CUST-00001',
                    'walletId' => 'wlt-uuid',
                ],
            ]),
        ]);

        (new HttpEnrollApi(app(KomopayClient::class)))->enrollCustomer([
            'fullName' => 'Test Client',
            'dateOfBirth' => '2000-01-01',
            'phoneCountryCode' => '269',
            'phoneNumber' => '3201234',
            'nationalIdNumber' => 'KM-1',
            'nationalIdType' => 'NATIONAL_ID',
            'addressIsland' => null,
            'kycDocuments' => [['type' => 'NATIONAL_ID']], // <- not a spec field, must be dropped
        ]);

        Http::assertSent(function ($request): bool {
            $body = $request->data();

            return ! array_key_exists('kycDocuments', $body)
                && ! array_key_exists('addressIsland', $body)
                && $body['fullName'] === 'Test Client'
                && $body['dateOfBirth'] === '2000-01-01'
                && $body['nationalIdType'] === 'NATIONAL_ID';
        });
    }

    public function test_kyc_upload_uses_multipart_with_document_type_and_file_fields(): void
    {
        config(['komopay.base_url' => 'https://api.lipa.test']);
        session(['agent_access_token' => 'jwt-access']);

        Http::fake([
            'https://api.lipa.test/api/v1/agent/customers/cust-uuid/kyc-documents' => Http::response([
                'data' => ['id' => 'doc-uuid', 'status' => 'PENDING_REVIEW'],
            ]),
        ]);

        $file = UploadedFile::fake()->create('id.pdf', 12, 'application/pdf');

        (new HttpEnrollApi(app(KomopayClient::class)))->uploadKycDocument('cust-uuid', 'NATIONAL_ID', $file);

        Http::assertSent(function ($request): bool {
            $contentType = $request->header('Content-Type')[0] ?? '';

            return str_starts_with($contentType, 'multipart/form-data')
                && str_contains((string) $request->body(), 'documentType')
                && str_contains((string) $request->body(), 'NATIONAL_ID');
        });
    }

    public function test_cash_out_sends_idempotency_key_and_merchant_uuid_payload(): void
    {
        config(['komopay.base_url' => 'https://api.lipa.test']);
        session(['agent_access_token' => 'jwt-access']);

        Http::fake([
            'https://api.lipa.test/api/v1/agent/cash-out' => Http::response([
                'data' => [
                    'transactionId' => 'txn-uuid',
                    'status' => 'COMPLETED',
                    'requestedAmount' => 50000,
                    'feeAmount' => 500,
                    'commissionAmount' => 500,
                    'netAmountToDestination' => 49500,
                    'currency' => 'KMF',
                    'completedAt' => '2026-05-12T12:00:00Z',
                    'replayed' => false,
                ],
            ]),
        ]);

        (new HttpOperationsApi(app(KomopayClient::class)))->processCashOut([
            'merchantId' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            'amount' => 50000,
        ]);

        Http::assertSent(function ($request): bool {
            $hasIdempotency = ! empty($request->header('Idempotency-Key')[0] ?? '');
            $body = $request->data();

            return $hasIdempotency
                && $body['merchantId'] === 'a1b2c3d4-e5f6-7890-abcd-ef1234567890'
                && $body['amount'] === 50000;
        });
    }

    public function test_limits_endpoint_returns_spec_flat_shape(): void
    {
        config(['komopay.base_url' => 'https://api.lipa.test']);
        session(['agent_access_token' => 'jwt-access']);

        Http::fake([
            'https://api.lipa.test/api/v1/agent/limits' => Http::response([
                'data' => [
                    'limitProfileId' => 'lp-uuid',
                    'profileName' => 'Agent Standard',
                    'maxTransactionAmount' => 200000,
                    'minTransactionAmount' => 500,
                    'maxDailyAmount' => 800000,
                    'maxWeeklyAmount' => 3000000,
                    'maxMonthlyAmount' => 8000000,
                    'maxDailyTransactionCount' => 50,
                    'maxMonthlyTransactionCount' => 800,
                    'requiredKycLevel' => 'KYC_BASIC',
                ],
            ]),
        ]);

        $limits = (new HttpLimitsApi(app(KomopayClient::class)))->getLimits();

        $this->assertSame('Agent Standard', $limits['profileName']);
        $this->assertSame(200000, $limits['maxTransactionAmount']);
        $this->assertArrayNotHasKey('float', $limits);
        $this->assertArrayNotHasKey('cashIn', $limits);
    }
}
