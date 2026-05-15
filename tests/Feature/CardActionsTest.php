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
                => Http::response(['data' => [
                    'cardId'                   => 'card-uuid',
                    'nfcUid'                   => '04AABBCCDDEE12',
                    'internalCardLast4'        => '0099',
                    'maskedInternalCardNumber' => '**** 0099',
                    'cardType'                 => 'STANDARD',
                    'status'                   => 'LOST',
                    'customerId'               => 'cust-uuid',
                    'customerFullName'         => 'Ali Hassan',
                    'customerPhoneMasked'      => '+269 **** 1234',
                    'expiresAt'                => '2028-05-12',
                ]]),
        ]);

        $card = (new HttpCardApi(app(KomopayClient::class)))->reportLost('cust-uuid', 'card-uuid');

        $this->assertSame('card-uuid', $card['cardId']);
        $this->assertSame('LOST', $card['status']);
        $this->assertSame('Ali Hassan', $card['customerFullName']);
        $this->assertSame('**** 0099', $card['maskedInternalCardNumber']);

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
                => Http::response(['data' => [
                    'cardId'                   => 'card-uuid',
                    'nfcUid'                   => '04AABBCCDDEE12',
                    'internalCardLast4'        => '0099',
                    'maskedInternalCardNumber' => '**** 0099',
                    'cardType'                 => 'STANDARD',
                    'status'                   => 'STOLEN',
                    'customerId'               => 'cust-uuid',
                    'customerFullName'         => 'Ali Hassan',
                    'customerPhoneMasked'      => '+269 **** 1234',
                    'expiresAt'                => '2028-05-12',
                ]]),
        ]);

        $card = (new HttpCardApi(app(KomopayClient::class)))->reportStolen('cust-uuid', 'card-uuid');

        $this->assertSame('STOLEN', $card['status']);

        Http::assertSent(function ($request): bool {
            return $request->method() === 'POST'
                && $request->url() === 'https://api.lipa.test/api/v1/agent/customers/cust-uuid/cards/card-uuid/report-stolen'
                && empty($request->header('Idempotency-Key')[0] ?? null);
        });
    }

    public function test_lookup_card_returns_enriched_card_lookup_response(): void
    {
        Http::fake([
            'https://api.lipa.test/api/v1/agent/cards/lookup*'
                => Http::response(['data' => [
                    'cardId'                   => 'card-uuid',
                    'nfcUid'                   => '04AABBCCDDEE12',
                    'internalCardLast4'        => '0099',
                    'maskedInternalCardNumber' => '**** 0099',
                    'cardType'                 => 'STANDARD',
                    'status'                   => 'ACTIVE',
                    'customerId'               => 'cust-uuid',
                    'customerFullName'         => 'Ali Hassan',
                    'customerPhoneMasked'      => '+269 **** 1234',
                    'expiresAt'                => '2028-05-12',
                ]]),
        ]);

        $card = (new HttpCardApi(app(KomopayClient::class)))->lookupCard('04AABBCCDDEE12');

        $this->assertNotNull($card);
        $this->assertSame('card-uuid', $card['cardId']);
        $this->assertSame('Ali Hassan', $card['customerFullName']);
        $this->assertSame('+269 **** 1234', $card['customerPhoneMasked']);
        $this->assertSame('**** 0099', $card['maskedInternalCardNumber']);
        $this->assertSame('STANDARD', $card['cardType']);
        $this->assertSame('ACTIVE', $card['status']);

        Http::assertSent(function ($request): bool {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return $request->method() === 'GET'
                && str_starts_with($request->url(), 'https://api.lipa.test/api/v1/agent/cards/lookup')
                && (($request->header('Authorization')[0] ?? null) === 'Bearer jwt-access')
                && (($query['nfcUid'] ?? null) === '04AABBCCDDEE12');
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
