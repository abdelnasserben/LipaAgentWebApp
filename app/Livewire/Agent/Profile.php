<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Contracts\Api\AgentApi;
use App\Contracts\Api\LimitsApi;
use App\Contracts\Api\TotpApi;
use App\Exceptions\ApiException;
use App\Livewire\Concerns\HandlesApiErrors;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Profil')]
class Profile extends Component
{
    use HandlesApiErrors;

    public array $profile = [];
    public array $limits = [];
    public ?string $apiError = null;
    public string $activeTab = 'profile';

    public bool $totpSetupOpen = false;
    public ?string $totpSecret = null;
    public ?string $totpQrUri = null;
    public string $totpQrCodeUrl = '';
    public string $totpQrCodeSvg = '';
    public string $totpCode = '';
    public ?string $totpError = null;
    public ?string $totpSuccess = null;
    public bool $showSignOutConfirm = false;

    public function mount(AgentApi $agent, LimitsApi $limits): void
    {
        $this->profile = $this->defaultProfile();
        $this->limits = $this->defaultLimits();

        try {
            $this->profile = $agent->getProfile();
            $this->limits = $limits->getLimits();
        } catch (ApiException $exception) {
            $this->showApiError($exception);
        }
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function toggleTotpSetup(TotpApi $totp): void
    {
        $this->totpError = null;
        $this->totpSuccess = null;

        if ($this->totpSetupOpen) {
            $this->resetTotpState();
            return;
        }

        try {
            $result = $totp->setup();
        } catch (ApiException $exception) {
            $this->showApiError($exception);
            return;
        }

        $this->totpSecret = $result['secret'];
        $this->totpQrUri = $result['qrUri'];
        $this->totpQrCodeUrl = '';
        $this->totpQrCodeSvg = $this->renderQrSvg($result['qrUri']);
        $this->totpSetupOpen = true;
    }

    public function confirmTotpSetup(TotpApi $totp): void
    {
        $this->totpError = null;

        $this->validate(
            ['totpCode' => 'required|digits:6'],
            [
                'totpCode.required' => 'Le code à 6 chiffres est requis.',
                'totpCode.digits'   => 'Le code doit comporter exactement 6 chiffres.',
            ],
        );

        try {
            $totp->confirm($this->totpCode);
        } catch (ApiException $exception) {
            if ($exception->apiCode() === 'MFA_INVALID') {
                $this->totpError = 'Code TOTP invalide. Vérifiez le code généré par votre application.';
                return;
            }
            $this->showApiError($exception);
            return;
        }

        $this->totpSuccess = 'Authentification TOTP activée avec succès.';
        $this->resetTotpState();
    }

    private function renderQrSvg(string $uri): string
    {
        try {
            $builder = new Builder(
                writer: new SvgWriter(),
                data: $uri,
                size: 220,
                margin: 8,
            );

            return $builder->build()->getString();
        } catch (\Throwable $e) {
            report($e);
            return '';
        }
    }

    private function resetTotpState(): void
    {
        $this->totpSetupOpen = false;
        $this->totpSecret = null;
        $this->totpQrUri = null;
        $this->totpQrCodeUrl = '';
        $this->totpQrCodeSvg = '';
        $this->totpCode = '';
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.agent.profile');
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultProfile(): array
    {
        return [
            'fullName' => 'Agent',
            'externalRef' => '-',
            'status' => 'PENDING_KYC',
            'phoneCountryCode' => '',
            'phoneNumber' => '',
            'zone' => '-',
            'kycLevel' => 'KYC_NONE',
            'contractRef' => null,
            'createdAt' => now()->toIso8601String(),
            'canDoCashIn' => false,
            'canDoCashOut' => false,
            'canSellCards' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultLimits(): array
    {
        return [
            'limitProfileId' => null,
            'profileName' => '—',
            'maxTransactionAmount' => null,
            'minTransactionAmount' => null,
            'maxDailyAmount' => null,
            'maxWeeklyAmount' => null,
            'maxMonthlyAmount' => null,
            'maxDailyTransactionCount' => null,
            'maxMonthlyTransactionCount' => null,
            'requiredKycLevel' => '—',
        ];
    }
}
