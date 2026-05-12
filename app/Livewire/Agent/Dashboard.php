<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Contracts\Api\AgentApi;
use App\Contracts\Api\TransactionApi;
use App\Exceptions\ApiException;
use App\Livewire\Concerns\HandlesApiErrors;
use Carbon\Carbon;
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

    /**
     * Real per-type totals for today, derived from the transactions list since
     * the daily-summary endpoint only exposes a global completed amount.
     *
     * @var array<string, int>
     */
    public array $todayTotalsByType = [
        'CASH_IN'   => 0,
        'CASH_OUT'  => 0,
        'CARD_SALE' => 0,
    ];

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
            $all = $result['data'] ?? [];

            $this->recentTransactions = array_slice($all, 0, 5);
            $this->todayTotalsByType = $this->computeTodayTotalsByType($all);
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
     * Sum completed transaction amounts by type, restricted to the current
     * calendar day. No estimation, no ratio: the absent type stays at 0.
     *
     * @param array<int, array<string, mixed>> $transactions
     * @return array<string, int>
     */
    private function computeTodayTotalsByType(array $transactions): array
    {
        $totals = [
            'CASH_IN'   => 0,
            'CASH_OUT'  => 0,
            'CARD_SALE' => 0,
        ];

        $today = Carbon::today();

        foreach ($transactions as $txn) {
            if (($txn['status'] ?? null) !== 'COMPLETED') {
                continue;
            }

            $createdAt = $txn['createdAt'] ?? null;
            if ($createdAt === null) {
                continue;
            }

            if (! Carbon::parse($createdAt)->isSameDay($today)) {
                continue;
            }

            $type = $txn['type'] ?? null;
            if (! is_string($type) || ! array_key_exists($type, $totals)) {
                continue;
            }

            $totals[$type] += (int) ($txn['requestedAmount'] ?? 0);
        }

        return $totals;
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
