<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Contracts\Api\CommissionApi;
use App\Exceptions\ApiException;
use App\Livewire\Concerns\HandlesApiErrors;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Commission')]
class Commission extends Component
{
    use HandlesApiErrors;

    public array $summary = [];

    public array $commissions = [];

    public ?string $apiError = null;

    public string $filterStatus = '';

    public function mount(CommissionApi $commissions): void
    {
        $this->loadData($commissions);
    }

    public function updatedFilterStatus(CommissionApi $commissions): void
    {
        $this->clearApiError();

        try {
            $result = $commissions->getCommissions(['status' => $this->filterStatus]);
        } catch (ApiException $exception) {
            $this->commissions = [];
            $this->showApiError($exception);

            return;
        }

        $this->commissions = $result['data'] ?? [];
    }

    public function loadData(CommissionApi $commissions): void
    {
        $this->clearApiError();

        try {
            $this->summary = $commissions->getSummary();
            $result = $commissions->getCommissions(['status' => $this->filterStatus]);
            $this->commissions = $result['data'] ?? [];
        } catch (ApiException $exception) {
            $this->summary = [
                'pendingTotal' => 0,
                'todayEarned' => 0,
                'weekEarned' => 0,
                'monthEarned' => 0,
            ];
            $this->commissions = [];
            $this->showApiError($exception);
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.agent.commission');
    }
}
