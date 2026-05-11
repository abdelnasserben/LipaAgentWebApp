<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Contracts\Api\TransactionApi;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Transactions')]
class Transactions extends Component
{
    public array $transactions = [];

    public array $pagination = [];

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
        $result = $transactions->getTransactions([
            'status' => $this->filterStatus,
            'type'   => $this->filterType,
            'search' => $this->search,
        ]);

        $data = $result['data'];

        if ($this->search !== '') {
            $needle = mb_strtolower($this->search);
            $data   = array_values(array_filter(
                $data,
                fn (array $t): bool => str_contains(mb_strtolower($t['description'] ?? ''), $needle)
            ));
        }

        $this->transactions = $data;
        $this->pagination   = $result['pagination'];
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
        return view('livewire.agent.transactions');
    }
}
