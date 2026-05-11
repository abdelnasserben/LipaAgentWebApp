<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Services\Mock\AgentService;
use App\Services\Mock\TransactionService;
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

    public function mount(): void
    {
        $agentService = new AgentService();
        $txnService   = new TransactionService();

        $this->profile = $agentService->getProfile();
        $this->balance = $agentService->getBalance();
        $this->summary = $agentService->getDailySummary();
        $this->recentTransactions = array_slice($txnService->getTransactions()['data'], 0, 5);
    }

    public function toggleBalance(): void
    {
        $this->balanceVisible = ! $this->balanceVisible;
    }

    public function selectTransaction(string $id): void
    {
        $txnService = new TransactionService();
        $this->selectedTransaction = $txnService->getTransaction($id);
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
