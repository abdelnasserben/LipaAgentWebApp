<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Services\Mock\AgentService;
use App\Services\Mock\LimitsService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Profil')]
class Profile extends Component
{
    public array $profile = [];
    public array $limits = [];
    public string $activeTab = 'profile'; // profile | limits | security

    public bool $totpSetupOpen = false;
    public bool $showSignOutConfirm = false;

    public function mount(): void
    {
        $this->profile = (new AgentService())->getProfile();
        $this->limits  = (new LimitsService())->getLimits();
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
}
