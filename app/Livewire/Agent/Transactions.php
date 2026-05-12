<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Contracts\Api\TransactionApi;
use App\Exceptions\ApiException;
use App\Livewire\Concerns\HandlesApiErrors;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Transactions')]
class Transactions extends Component
{
    use HandlesApiErrors;

    public array $transactions = [];

    public array $pagination = [];

    public ?string $apiError = null;

    public string $search = '';

    public string $filterStatus = '';

    public string $filterType = '';

    public ?array $selectedTransaction = null;

    public function mount(TransactionApi $transactions): void
    {
        $this->loadTransactions($transactions);
    }

    public function updatedSearch(TransactionApi $transactions): void
    {
        $this->loadTransactions($transactions);
    }

    public function updatedFilterStatus(TransactionApi $transactions): void
    {
        $this->loadTransactions($transactions);
    }

    public function updatedFilterType(TransactionApi $transactions): void
    {
        $this->loadTransactions($transactions);
    }

    public function loadTransactions(TransactionApi $transactions): void
    {
        $this->clearApiError();

        try {
            $result = $transactions->getTransactions([
                'status' => $this->filterStatus,
                'type'   => $this->filterType,
                'search' => $this->search,
            ]);
        } catch (ApiException $exception) {
            $this->transactions = [];
            $this->pagination = [];
            $this->showApiError($exception);

            return;
        }

        $data = $result['data'] ?? [];

        if ($this->search !== '') {
            $needle = mb_strtolower($this->search);
            $data   = array_values(array_filter(
                $data,
                fn (array $t): bool => str_contains(mb_strtolower($t['description'] ?? ''), $needle)
            ));
        }

        $this->transactions = $data;
        $this->pagination   = $result['pagination'] ?? [];
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
        return view('livewire.agent.transactions');
    }
}
