<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Services\Mock\TransactionService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Relevé')]
class Statement extends Component
{
    public array $entries = [];

    public array $pagination = [];

    public string $filterFrom = '';

    public string $filterTo = '';

    public ?array $selectedEntry = null;

    public function mount(): void
    {
        $this->loadEntries();
    }

    public function updatedFilterFrom(): void
    {
        $this->loadEntries();
    }

    public function updatedFilterTo(): void
    {
        $this->loadEntries();
    }

    public function loadEntries(): void
    {
        $service = new TransactionService();

        $result = $service->getStatements([
            'from' => $this->filterFrom,
            'to'   => $this->filterTo,
        ]);

        $this->entries    = $result['data'];
        $this->pagination = $result['pagination'];
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
