<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\Api\AgentAuthApi;
use App\Exceptions\AgentAuthException;
use App\Livewire\Auth\Login;
use App\Services\Api\Http\HttpAgentAuthApi;
use App\Services\Api\Http\KomopayClient;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AgentLoginTest extends TestCase
{
    public function test_login_screen_is_pin_first_not_sms_otp(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('PIN Agent')
            ->assertSee('Se connecter')
            ->assertDontSee('Recevoir le code')
            ->assertDontSee('Verifiez vos SMS');
    }

    public function test_pin_login_stores_tokens_and_redirects_without_mfa(): void
    {
        $this->app->instance(AgentAuthApi::class, new class implements AgentAuthApi
        {
            public function login(string $phoneCountryCode, string $phoneNumber, string $pin): array
            {
                return [
                    'mfaRequired' => false,
                    'tokens' => [
                        'accessToken' => 'jwt-access',
                        'accessTokenExpiresAt' => '2026-05-12T20:00:00Z',
                        'refreshToken' => 'refresh-token',
                        'refreshTokenExpiresAt' => '2026-06-11T12:00:00Z',
                    ],
                ];
            }

            public function verifyMfa(string $challengeId, string $code): array
            {
                throw new AgentAuthException('MFA_INVALID', 401);
            }

            public function logout(): void {}
        });

        Livewire::test(Login::class)
            ->set('phoneNumber', '3201456')
            ->set('pin', '1234')
            ->call('login')
            ->assertRedirect(route('dashboard'));

        $this->assertTrue(session('agent_authenticated'));
        $this->assertSame('2693201456', session('agent_phone'));
        $this->assertSame('jwt-access', session('agent_access_token'));
        $this->assertSame('refresh-token', session('agent_refresh_token'));
    }

    public function test_login_normalizes_full_phone_number_before_calling_api(): void
    {
        $spy = new class
        {
            public array $payload = [];
        };

        $this->app->instance(AgentAuthApi::class, new class($spy) implements AgentAuthApi
        {
            public function __construct(private readonly object $spy) {}

            public function login(string $phoneCountryCode, string $phoneNumber, string $pin): array
            {
                $this->spy->payload = [
                    'phoneCountryCode' => $phoneCountryCode,
                    'phoneNumber' => $phoneNumber,
                    'pin' => $pin,
                ];

                return [
                    'mfaRequired' => false,
                    'tokens' => [
                        'accessToken' => 'jwt-access',
                        'accessTokenExpiresAt' => '2026-05-12T20:00:00Z',
                        'refreshToken' => 'refresh-token',
                        'refreshTokenExpiresAt' => '2026-06-11T12:00:00Z',
                    ],
                ];
            }

            public function verifyMfa(string $challengeId, string $code): array
            {
                throw new AgentAuthException('MFA_INVALID', 401);
            }

            public function logout(): void {}
        });

        Livewire::test(Login::class)
            ->set('phoneNumber', '+269 320 1456')
            ->set('pin', ' 1234 ')
            ->call('login')
            ->assertRedirect(route('dashboard'));

        $this->assertSame([
            'phoneCountryCode' => '269',
            'phoneNumber' => '3201456',
            'pin' => '1234',
        ], $spy->payload);
    }

    public function test_pin_login_can_continue_with_totp_mfa(): void
    {
        $this->app->instance(AgentAuthApi::class, new class implements AgentAuthApi
        {
            public function login(string $phoneCountryCode, string $phoneNumber, string $pin): array
            {
                return [
                    'mfaRequired' => true,
                    'challengeId' => 'challenge-123',
                    'mfaFactor' => 'TOTP',
                ];
            }

            public function verifyMfa(string $challengeId, string $code): array
            {
                if ($challengeId !== 'challenge-123' || $code !== '123456') {
                    throw new AgentAuthException('MFA_INVALID', 401);
                }

                return [
                    'mfaRequired' => false,
                    'tokens' => [
                        'accessToken' => 'mfa-access',
                        'accessTokenExpiresAt' => '2026-05-12T20:00:00Z',
                        'refreshToken' => 'mfa-refresh',
                        'refreshTokenExpiresAt' => '2026-06-11T12:00:00Z',
                    ],
                ];
            }

            public function logout(): void {}
        });

        Livewire::test(Login::class)
            ->set('phoneNumber', '3201456')
            ->set('pin', '4321')
            ->call('login')
            ->assertSet('step', 'mfa')
            ->assertSee('Verification TOTP')
            ->set('totpCode', '123456')
            ->call('verifyMfa')
            ->assertRedirect(route('dashboard'));

        $this->assertSame('mfa-access', session('agent_access_token'));
    }

    public function test_invalid_credentials_are_displayed_cleanly(): void
    {
        $this->app->instance(AgentAuthApi::class, new class implements AgentAuthApi
        {
            public function login(string $phoneCountryCode, string $phoneNumber, string $pin): array
            {
                throw new AgentAuthException('INVALID_CREDENTIALS', 401);
            }

            public function verifyMfa(string $challengeId, string $code): array
            {
                throw new AgentAuthException('MFA_INVALID', 401);
            }

            public function logout(): void {}
        });

        Livewire::test(Login::class)
            ->set('phoneNumber', '3201456')
            ->set('pin', '9999')
            ->call('login')
            ->assertSet('error', 'Identifiants invalides. Verifiez le telephone et le PIN.');
    }

    public function test_http_auth_uses_spec_login_and_verify_mfa_paths(): void
    {
        config(['komopay.base_url' => 'https://api.lipa.test']);

        Http::fake([
            'https://api.lipa.test/api/v1/auth/agent/login' => Http::response([
                'data' => [
                    'mfaRequired' => true,
                    'challengeId' => 'challenge-123',
                    'mfaFactor' => 'TOTP',
                ],
                'timestamp' => '2026-05-12T12:00:00Z',
            ]),
            'https://api.lipa.test/api/v1/auth/agent/login/verify-mfa' => Http::response([
                'data' => [
                    'mfaRequired' => false,
                    'tokens' => [
                        'accessToken' => 'jwt',
                        'accessTokenExpiresAt' => '2026-05-12T20:00:00Z',
                        'refreshToken' => 'refresh',
                        'refreshTokenExpiresAt' => '2026-06-11T12:00:00Z',
                    ],
                ],
                'timestamp' => '2026-05-12T12:00:00Z',
            ]),
        ]);

        $api = new HttpAgentAuthApi(app(KomopayClient::class));

        $login = $api->login('269', '3201456', '1234');
        $verified = $api->verifyMfa('challenge-123', '123456');

        $this->assertTrue($login['mfaRequired']);
        $this->assertFalse($verified['mfaRequired']);

        Http::assertSent(fn ($request) => $request->url() === 'https://api.lipa.test/api/v1/auth/agent/login'
            && $request['phoneCountryCode'] === '269'
            && $request['phoneNumber'] === '3201456'
            && $request['pin'] === '1234');

        Http::assertSent(fn ($request) => $request->url() === 'https://api.lipa.test/api/v1/auth/agent/login/verify-mfa'
            && $request['challengeId'] === 'challenge-123'
            && $request['code'] === '123456');
    }

    public function test_http_auth_preserves_legacy_otp_removed_error_code(): void
    {
        config(['komopay.base_url' => 'https://api.lipa.test']);

        Http::fake([
            'https://api.lipa.test/api/v1/auth/agent/login' => Http::response([
                'error' => [
                    'code' => 'LEGACY_OTP_LOGIN_REMOVED',
                    'message' => 'Legacy OTP login removed',
                ],
            ], 410),
        ]);

        $this->expectException(AgentAuthException::class);
        $this->expectExceptionMessage('Legacy OTP login removed');

        try {
            (new HttpAgentAuthApi(app(KomopayClient::class)))->login('269', '3201456', '1234');
        } catch (AgentAuthException $exception) {
            $this->assertSame('LEGACY_OTP_LOGIN_REMOVED', $exception->apiCode());

            throw $exception;
        }
    }

    public function test_http_auth_does_not_report_missing_login_endpoint_as_invalid_credentials(): void
    {
        config(['komopay.base_url' => 'https://api.lipa.test']);

        Http::fake([
            'https://api.lipa.test/api/v1/auth/agent/login' => Http::response('', 404),
        ]);

        try {
            (new HttpAgentAuthApi(app(KomopayClient::class)))->login('269', '3201456', '1234');
        } catch (AgentAuthException $exception) {
            $this->assertSame('AUTH_ENDPOINT_NOT_FOUND', $exception->apiCode());
            $this->assertSame(404, $exception->statusCode());

            return;
        }

        $this->fail('Expected auth exception for missing login endpoint.');
    }
}
