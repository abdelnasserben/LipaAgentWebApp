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
#[Title('Relevé')]
class Statement extends Component
{
    use HandlesApiErrors;

    public array $entries = [];

    public array $pagination = [];

    public ?string $apiError = null;

    public string $filterFrom = '';

    public string $filterTo = '';

    public ?array $selectedEntry = null;

    public function mount(TransactionApi $transactions): void
    {
        $this->loadEntries($transactions);
    }

    public function updatedFilterFrom(TransactionApi $transactions): void
    {
        $this->loadEntries($transactions);
    }

    public function updatedFilterTo(TransactionApi $transactions): void
    {
        $this->loadEntries($transactions);
    }

    public function loadEntries(TransactionApi $transactions): void
    {
        $this->clearApiError();

        try {
            $result = $transactions->getStatements([
                'from' => $this->filterFrom,
                'to'   => $this->filterTo,
            ]);
        } catch (ApiException $exception) {
            $this->entries = [];
            $this->pagination = [];
            $this->showApiError($exception);

            return;
        }

        $this->entries    = $result['data'] ?? [];
        $this->pagination = $result['pagination'] ?? [];
    }

    public function selectEntry(string $id): void
    {
        foreach ($this->entries as $entry) {
            if ($entry['id'] === $id) {
                $this->selectedEntry = $entry;

                return;
            }
        }
    }

    public function closeEntry(): void
    {
        $this->selectedEntry = null;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.agent.statement');
    }
}
