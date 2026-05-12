<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Api\Http\HttpCardApi;
use App\Services\Api\Http\KomopayClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CardActionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['komopay.base_url' => 'https://api.lipa.test']);
        session(['agent_access_token' => 'jwt-access']);
    }

    public function test_report_lost_posts_to_spec_path_with_bearer_token_and_no_body(): void
    {
        Http::fake([
            'https://api.lipa.test/api/v1/agent/customers/cust-uuid/cards/card-uuid/report-lost'
                => Http::response(['data' => ['id' => 'card-uuid', 'status' => 'LOST']]),
        ]);

        $card = (new HttpCardApi(app(KomopayClient::class)))->reportLost('cust-uuid', 'card-uuid');

        $this->assertSame('LOST', $card['status']);

        Http::assertSent(function ($request): bool {
            return $request->method() === 'POST'
                && $request->url() === 'https://api.lipa.test/api/v1/agent/customers/cust-uuid/cards/card-uuid/report-lost'
                && (($request->header('Authorization')[0] ?? null) === 'Bearer jwt-access')
                && empty($request->header('Idempotency-Key')[0] ?? null)
                && $request->body() === '[]';
        });
    }

    public function test_report_stolen_posts_to_spec_path_without_idempotency_key(): void
    {
        Http::fake([
            'https://api.lipa.test/api/v1/agent/customers/cust-uuid/cards/card-uuid/report-stolen'
                => Http::response(['data' => ['id' => 'card-uuid', 'status' => 'STOLEN']]),
        ]);

        $card = (new HttpCardApi(app(KomopayClient::class)))->reportStolen('cust-uuid', 'card-uuid');

        $this->assertSame('STOLEN', $card['status']);

        Http::assertSent(function ($request): bool {
            return $request->method() === 'POST'
                && $request->url() === 'https://api.lipa.test/api/v1/agent/customers/cust-uuid/cards/card-uuid/report-stolen'
                && empty($request->header('Idempotency-Key')[0] ?? null);
        });
    }

    public function test_replace_card_sends_idempotency_key_and_spec_body(): void
    {
        Http::fake([
            'https://api.lipa.test/api/v1/agent/customers/cust-uuid/cards/card-uuid/replace'
                => Http::response(['data' => [
                    'transactionId' => 'txn-uuid',
                    'status'        => 'COMPLETED',
                    'newCardId'     => 'new-card-uuid',
                    'oldCardId'     => 'card-uuid',
                    'customerId'    => 'cust-uuid',
                    'replacementFee' => 5000,
                    'replayed'      => false,
                ]]),
        ]);

        $result = (new HttpCardApi(app(KomopayClient::class)))->replaceCard('cust-uuid', 'card-uuid', [
            'stockId'        => 'stk-uuid',
            'replacementFee' => 5000,
        ]);

        $this->assertSame('new-card-uuid', $result['newCardId']);
        $this->assertSame('card-uuid', $result['oldCardId']);

        Http::assertSent(function ($request): bool {
            $body = $request->data();

            return $request->method() === 'POST'
                && $request->url() === 'https://api.lipa.test/api/v1/agent/customers/cust-uuid/cards/card-uuid/replace'
                && ! empty($request->header('Idempotency-Key')[0] ?? '')
                && $body === ['stockId' => 'stk-uuid', 'replacementFee' => 5000];
        });
    }
}
