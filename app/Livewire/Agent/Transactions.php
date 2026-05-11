<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Services\Mock\TransactionService;
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

    public function mount(): void
    {
        $this->loadTransactions();
    }

    public function updatedSearch(): void
    {
        $this->loadTransactions();
    }

    public function updatedFilterStatus(): void
    {
        $this->loadTransactions();
    }

    public function updatedFilterType(): void
    {
        $this->loadTransactions();
    }

    public function loadTransactions(): void
    {
        $service = new TransactionService();

        $result = $service->getTransactions([
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

    public function selectTransaction(string $id): void
    {
        $service = new TransactionService();
        $this->selectedTransaction = $service->getTransaction($id);
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
