<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Contracts\Api\AgentApi;
use App\Contracts\Api\TransactionApi;
use App\Exceptions\ApiException;
use App\Livewire\Concerns\HandlesApiErrors;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Accueil')]
class Dashboard extends Component
{
    use HandlesApiErrors;

    public array $profile = [];
    public array $balance = [];
    public array $summary = [];
    public array $recentTransactions = [];

    public ?string $apiError = null;

    public bool $balanceVisible = true;
    public ?array $selectedTransaction = null;

    public function mount(AgentApi $agent, TransactionApi $transactions): void
    {
        $this->profile = $this->defaultProfile();
        $this->balance = $this->defaultBalance();
        $this->summary = $this->defaultSummary();

        try {
            $this->profile = $agent->getProfile();
            $this->balance = $agent->getBalance();
            $this->summary = $agent->getDailySummary();

            $result = $transactions->getTransactions();
            $this->recentTransactions = array_slice($result['data'] ?? [], 0, 5);
        } catch (ApiException $exception) {
            $this->showApiError($exception);
        }
    }

    public function toggleBalance(): void
    {
        $this->balanceVisible = ! $this->balanceVisible;
    }

    public function selectTransaction(string $id, TransactionApi $transactions): void
    {
        $this->clearApiError();

        try {
            $this->selectedTransaction = $transactions->getTransaction($id);
        } catch (ApiException $exception) {
            $this->showApiError($exception);
        }
    }

    public function closeTransaction(): void
    {
        $this->selectedTransaction = null;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.agent.dashboard');
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultProfile(): array
    {
        return [
            'fullName' => 'Agent',
            'status' => 'PENDING_KYC',
            'floatAlertThreshold' => 0,
            'externalRef' => '-',
            'zone' => '-',
            'kycLevel' => 'KYC_NONE',
            'contractRef' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultBalance(): array
    {
        return [
            'availableBalance' => 0,
            'frozenBalance' => 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultSummary(): array
    {
        return [
            'belowFloatAlert' => false,
            'totalCompletedAmountToday' => 0,
            'totalCompletedCountToday' => 0,
            'commissionEarnedToday' => 0,
        ];
    }
}
