<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Contracts\Api\CommissionApi;
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

    public function mount(CommissionApi $commissions): void
    {
        $this->loadData($commissions);
    }

    public function updatedFilterStatus(CommissionApi $commissions): void
    {
        $result = $commissions->getCommissions(['status' => $this->filterStatus]);

        $this->commissions = $result['data'];
    }

    public function loadData(CommissionApi $commissions): void
    {
        $this->summary     = $commissions->getSummary();
        $result            = $commissions->getCommissions(['status' => $this->filterStatus]);
        $this->commissions = $result['data'];
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.agent.commission');
    }
}
