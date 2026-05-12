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

    public string $step = 'credentials'; // credentials | mfa

    public string $phoneCountryCode = '269';

    public string $phoneNumber = '';

    public string $pin = '';

    public string $challengeId = '';

    public string $totpCode = '';

    public ?string $error = null;

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

        if (($result['mfaRequired'] ?? false) === true) {
            $this->startMfaStep($result);

            return;
        }

        $this->completeLogin($result);
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
        $this->step = 'credentials';
        $this->challengeId = '';
        $this->totpCode = '';
        $this->error = null;
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
            'pin.required' => 'PIN Agent requis.',
            'pin.regex' => 'Le PIN Agent doit contenir 4 a 8 chiffres.',
            'totpCode.required' => 'Code TOTP requis.',
            'totpCode.regex' => 'Le code TOTP doit contenir 6 chiffres.',
        ];
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }
}
