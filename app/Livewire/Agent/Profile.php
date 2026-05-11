<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Contracts\Api\AgentApi;
use App\Contracts\Api\LimitsApi;
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

    public function mount(AgentApi $agent, LimitsApi $limits): void
    {
        $this->profile = $agent->getProfile();
        $this->limits  = $limits->getLimits();
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
