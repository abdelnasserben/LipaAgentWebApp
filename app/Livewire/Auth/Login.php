<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Contracts\Api\AgentAuthApi;
use App\Exceptions\ApiException;
use App\Livewire\Concerns\HandlesApiErrors;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Connexion Agent')]
class Login extends Component
{
    use HandlesApiErrors;

    public string $step = 'credentials'; // credentials | mfa | pin-setup

    public string $phoneCountryCode = '269';

    public string $phoneNumber = '';

    public string $pin = '';

    public string $challengeId = '';

    public string $totpCode = '';

    public string $newPin = '';

    public string $confirmPin = '';

    public ?string $error = null;

    /**
     * Short-lived PIN_SETUP bearer token returned by the login response when
     * the Agent has no PIN yet. Kept only in Livewire state — never written
     * to the session as a normal access token.
     */
    public string $pinSetupToken = '';

    public function mount(): void
    {
        $flashError = session('api_error');
        if (is_string($flashError) && $flashError !== '') {
            $this->error = $flashError;
        }
    }

    public function login(AgentAuthApi $auth): void
    {
        $this->normalizeCredentials();

        $this->validate([
            'phoneCountryCode' => 'required|regex:/^\d{1,5}$/',
            'phoneNumber' => 'required|regex:/^\d{4,15}$/',
            'pin' => 'required|regex:/^\d{4,8}$/',
        ], $this->validationMessages());

        $this->error = null;

        try {
            $result = $auth->login($this->phoneCountryCode, $this->phoneNumber, $this->pin);
        } catch (ApiException $exception) {
            $this->showApiError($exception, 'error');

            return;
        }

        if (($result['pinSetupRequired'] ?? false) === true) {
            $this->startPinSetupStep($result);

            return;
        }

        if (($result['mfaRequired'] ?? false) === true) {
            session(['agent_totp_enrolled' => true]);
            $this->startMfaStep($result);

            return;
        }

        session(['agent_totp_enrolled' => false]);
        $this->completeLogin($result);
    }

    public function setupPin(AgentAuthApi $auth): void
    {
        if ($this->pinSetupToken === '') {
            $this->error = 'Session de definition de PIN expiree. Reconnectez-vous.';
            $this->restartFromCredentials();

            return;
        }

        $this->newPin = trim($this->newPin);
        $this->confirmPin = trim($this->confirmPin);

        $this->validate([
            'newPin' => 'required|regex:/^\d{4,8}$/',
            'confirmPin' => 'required|regex:/^\d{4,8}$/',
        ], $this->validationMessages());

        if ($this->newPin !== $this->confirmPin) {
            $this->addError('confirmPin', 'Le PIN et sa confirmation ne correspondent pas.');

            return;
        }

        $this->error = null;

        try {
            $auth->setupAuthPin($this->pinSetupToken, $this->newPin);
        } catch (ApiException $exception) {
            if (in_array($exception->apiCode(), ['AUTH_INVALID_TOKEN', 'UNAUTHORIZED', 'TOKEN_EXPIRED', 'TOKEN_REVOKED'], true)) {
                $this->error = 'Lien de definition de PIN expire. Reconnectez-vous pour recommencer.';
                $this->restartFromCredentials();

                return;
            }

            $this->showApiError($exception, 'error');
            $this->newPin = '';
            $this->confirmPin = '';

            return;
        }

        // PIN set successfully — return Agent to the login screen to sign in with the new PIN.
        $this->restartFromCredentials();
        session()->flash('api_error', 'PIN defini avec succes. Connectez-vous avec votre nouveau PIN.');
        $this->redirect(route('login'), navigate: true);
    }

    public function verifyMfa(AgentAuthApi $auth): void
    {
        $this->validate([
            'totpCode' => 'required|regex:/^\d{6}$/',
        ], $this->validationMessages());

        $this->error = null;

        try {
            $result = $auth->verifyMfa($this->challengeId, $this->totpCode);
        } catch (ApiException $exception) {
            $this->showApiError($exception, 'error');
            $this->totpCode = '';

            return;
        }

        $this->completeLogin($result);
    }

    public function back(): void
    {
        $this->restartFromCredentials();
    }

    private function restartFromCredentials(): void
    {
        $this->step = 'credentials';
        $this->challengeId = '';
        $this->totpCode = '';
        $this->pinSetupToken = '';
        $this->newPin = '';
        $this->confirmPin = '';
    }

    private function normalizeCredentials(): void
    {
        $countryCode = preg_replace('/\D+/', '', $this->phoneCountryCode) ?? '';
        $phoneNumber = preg_replace('/\D+/', '', $this->phoneNumber) ?? '';

        if (str_starts_with($phoneNumber, '00'.$countryCode)) {
            $phoneNumber = substr($phoneNumber, strlen('00'.$countryCode));
        }

        if ($countryCode !== '' && str_starts_with($phoneNumber, $countryCode)) {
            $phoneNumber = substr($phoneNumber, strlen($countryCode));
        }

        if (str_starts_with($phoneNumber, '0') && strlen($phoneNumber) > 7) {
            $phoneNumber = ltrim($phoneNumber, '0');
        }

        $this->phoneCountryCode = $countryCode;
        $this->phoneNumber = $phoneNumber;
        $this->pin = trim($this->pin);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function startMfaStep(array $result): void
    {
        if (($result['mfaFactor'] ?? 'TOTP') !== 'TOTP') {
            $this->error = "MFA requis, mais le facteur retourne n'est pas pris en charge.";

            return;
        }

        if (! is_string($result['challengeId'] ?? null) || $result['challengeId'] === '') {
            $this->error = 'MFA requis, mais le challenge est absent.';

            return;
        }

        $this->challengeId = $result['challengeId'];
        $this->totpCode = '';
        $this->step = 'mfa';
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function startPinSetupStep(array $result): void
    {
        $token = $result['pinSetupToken'] ?? null;

        if (! is_string($token) || $token === '') {
            $this->error = 'Reponse de configuration du PIN incomplete.';

            return;
        }

        $this->pinSetupToken = $token;
        $this->newPin = '';
        $this->confirmPin = '';
        $this->pin = '';
        $this->step = 'pin-setup';
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function completeLogin(array $result): void
    {
        $tokens = $result['tokens'] ?? null;

        if (! is_array($tokens) || ! is_string($tokens['accessToken'] ?? null)) {
            $this->error = 'Reponse de connexion incomplete.';

            return;
        }

        session([
            'agent_authenticated' => true,
            'agent_phone' => $this->phoneCountryCode.$this->phoneNumber,
            'agent_access_token' => $tokens['accessToken'],
            'agent_access_token_expires_at' => $tokens['accessTokenExpiresAt'] ?? null,
            'agent_refresh_token' => $tokens['refreshToken'] ?? null,
            'agent_refresh_token_expires_at' => $tokens['refreshTokenExpiresAt'] ?? null,
        ]);

        $this->redirect(route('dashboard'), navigate: true);
    }

    /**
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return [
            'phoneCountryCode.required' => 'Indicatif pays requis.',
            'phoneCountryCode.regex' => 'Indicatif pays invalide.',
            'phoneNumber.required' => 'Numero de telephone requis.',
            'phoneNumber.regex' => 'Numero de telephone invalide.',
            'pin.required' => 'PIN requis.',
            'pin.regex' => 'Le PIN doit contenir 4 à 8 chiffres.',
            'totpCode.required' => 'Code TOTP requis.',
            'totpCode.regex' => 'Le code TOTP doit contenir 6 chiffres.',
            'newPin.required' => 'Nouveau PIN requis.',
            'newPin.regex' => 'Le PIN doit contenir 4 à 8 chiffres.',
            'confirmPin.required' => 'Confirmation du PIN requise.',
            'confirmPin.regex' => 'La confirmation doit contenir 4 à 8 chiffres.',
        ];
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }
}
