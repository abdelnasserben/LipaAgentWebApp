<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Contracts\Api\AgentApi;
use App\Contracts\Api\LimitsApi;
use App\Exceptions\ApiException;
use App\Livewire\Concerns\HandlesApiErrors;
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
    public string $activeTab = 'profile'; // profile | limits | security

    public bool $totpSetupOpen = false;
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

    public function toggleTotpSetup(): void
    {
        $this->totpSetupOpen = ! $this->totpSetupOpen;
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
