<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Services\Mock\AgentAuthService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Connexion')]
class Login extends Component
{
    public string $step = 'phone'; // phone | otp

    public string $phoneCountryCode = '269';
    public string $phoneNumber = '';
    public string $challengeId = '';
    public string $otpCode = '';

    public ?string $error = null;
    public bool $loading = false;
    public int $resendCountdown = 0;

    protected array $rules = [
        'phoneCountryCode' => 'required|max:5',
        'phoneNumber'      => 'required|regex:/^\d{4,15}$/',
    ];

    public function requestOtp(): void
    {
        $this->validate([
            'phoneCountryCode' => 'required|max:5',
            'phoneNumber'      => 'required|regex:/^\d{4,15}$/',
        ]);

        $this->error = null;

        $auth = new AgentAuthService();
        $result = $auth->requestOtp($this->phoneCountryCode, $this->phoneNumber);

        if (! $result) {
            $this->error = 'Numéro non trouvé ou compte non actif.';
            return;
        }

        $this->challengeId = $result['challengeId'];
        $this->resendCountdown = 60;
        $this->step = 'otp';
    }

    public function verifyOtp(): void
    {
        $this->validate([
            'otpCode' => 'required|regex:/^\d{6}$/',
        ]);

        $this->error = null;

        $auth = new AgentAuthService();
        if (! $auth->verifyOtp($this->challengeId, $this->otpCode)) {
            $this->error = 'Code incorrect ou expiré.';
            return;
        }

        session([
            'agent_authenticated' => true,
            'agent_phone'         => $this->phoneCountryCode . $this->phoneNumber,
        ]);

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function back(): void
    {
        $this->step = 'phone';
        $this->otpCode = '';
        $this->error = null;
    }

    public function resend(): void
    {
        $this->requestOtp();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.auth.login');
    }
}
