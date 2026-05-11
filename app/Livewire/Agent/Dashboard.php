<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Contracts\Api\AgentApi;
use App\Contracts\Api\TransactionApi;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Accueil')]
class Dashboard extends Component
{
    public array $profile = [];
    public array $balance = [];
    public array $summary = [];
    public array $recentTransactions = [];

    public bool $balanceVisible = true;
    public ?array $selectedTransaction = null;

    public function mount(AgentApi $agent, TransactionApi $transactions): void
    {
        $this->profile            = $agent->getProfile();
        $this->balance            = $agent->getBalance();
        $this->summary            = $agent->getDailySummary();
        $this->recentTransactions = array_slice($transactions->getTransactions()['data'], 0, 5);
    }

    public function toggleBalance(): void
    {
        $this->balanceVisible = ! $this->balanceVisible;
    }

    public function selectTransaction(string $id, TransactionApi $transactions): void
    {
        $this->selectedTransaction = $transactions->getTransaction($id);
    }

    public function closeTransaction(): void
    {
        $this->selectedTransaction = null;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.agent.dashboard');
    }
}
