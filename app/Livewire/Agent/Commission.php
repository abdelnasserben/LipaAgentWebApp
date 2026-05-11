<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Services\Mock\CommissionService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Commission')]
class Commission extends Component
{
    public array $summary = [];

    public array $commissions = [];

    public string $filterStatus = '';

    public function mount(): void
    {
        $this->loadData();
    }

    public function updatedFilterStatus(): void
    {
        $service = new CommissionService();
        $result  = $service->getCommissions(['status' => $this->filterStatus]);

        $this->commissions = $result['data'];
    }

    public function loadData(): void
    {
        $service = new CommissionService();

        $this->summary     = $service->getSummary();
        $result            = $service->getCommissions(['status' => $this->filterStatus]);
        $this->commissions = $result['data'];
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.agent.commission');
    }
}
