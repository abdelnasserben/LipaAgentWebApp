<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Contracts\Api\OperationsApi;
use App\Exceptions\ApiException;
use App\Livewire\Concerns\HandlesApiErrors;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Opérations')]
class Operations extends Component
{
    use HandlesApiErrors;

    public string $activeTab = 'cash-in';

    public ?string $apiError = null;

    // Cash-in state
    public string $ciStep = 'lookup';
    public string $ciPhoneCountryCode = '269';
    public string $ciPhoneNumber = '';
    public ?array $ciCustomer = null;
    public string $ciAmount = '';
    public ?array $ciResult = null;
    public ?string $ciError = null;

    // Cash-out state
    public string $coStep = 'form';
    public string $coMerchantId = '';
    public string $coAmount = '';
    public ?array $coResult = null;
    public ?string $coError = null;
    public int $coStatus = 200;

    public function mount(string $tab = 'cash-in'): void
    {
        $this->activeTab = in_array($tab, ['cash-in', 'cash-out']) ? $tab : 'cash-in';
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetCashIn();
        $this->resetCashOut();
    }

    // ── Cash-in ───────────────────────────────────────────

    public function lookupCustomer(OperationsApi $operations): void
    {
        $this->validate([
            'ciPhoneNumber' => 'required|regex:/^\d{4,15}$/',
        ], [
            'ciPhoneNumber.required' => 'Le numéro est requis.',
            'ciPhoneNumber.regex'    => 'Numéro invalide (4 à 15 chiffres).',
        ]);

        $this->ciError = null;

        try {
            $customer = $operations->lookupCustomer($this->ciPhoneCountryCode, $this->ciPhoneNumber);
        } catch (ApiException $exception) {
            $this->showApiError($exception, 'ciError');

            return;
        }

        if (! $customer) {
            $this->ciError = 'Client introuvable pour ce numéro.';
            return;
        }

        if ($customer['status'] === 'SUSPENDED') {
            $this->ciError = 'Ce compte client est suspendu.';
            return;
        }

        $this->ciCustomer = $customer;
        $this->ciStep = 'confirm';
    }

    public function confirmCustomer(): void
    {
        $this->ciStep = 'amount';
    }

    public function backToLookup(): void
    {
        $this->ciStep = 'lookup';
        $this->ciCustomer = null;
        $this->ciError = null;
    }

    public function setCashInAmount(int $amount): void
    {
        $this->ciAmount = (string) $amount;
    }

    public function submitCashIn(OperationsApi $operations): void
    {
        $this->validate([
            'ciAmount' => 'required|integer|min:1',
        ], [
            'ciAmount.required' => 'Le montant est requis.',
            'ciAmount.integer'  => 'Le montant doit être un entier.',
            'ciAmount.min'      => 'Le montant doit être supérieur à 0.',
        ]);

        $this->ciError = null;

        try {
            $result = $operations->processCashIn([
                'customerId' => $this->ciCustomer['customerId'],
                'amount'     => (int) $this->ciAmount,
            ]);
        } catch (ApiException $exception) {
            $this->showApiError($exception, 'ciError');

            return;
        }

        $this->ciResult = $result;
        $this->ciStep = 'success';
    }

    public function resetCashIn(): void
    {
        $this->ciStep = 'lookup';
        $this->ciPhoneNumber = '';
        $this->ciCustomer = null;
        $this->ciAmount = '';
        $this->ciResult = null;
        $this->ciError = null;
    }

    // ── Cash-out ──────────────────────────────────────────

    public function confirmCashOut(): void
    {
        $this->validate(
            [
                'coMerchantId' => 'required|uuid',
                'coAmount'     => 'required|integer|min:1',
            ],
            [
                'coMerchantId.required' => "L'identifiant marchand est requis.",
                'coMerchantId.uuid'     => "L'identifiant marchand doit être un UUID valide.",
                'coAmount.required'     => 'Le montant est requis.',
                'coAmount.integer'      => 'Montant invalide.',
                'coAmount.min'          => 'Le montant doit être supérieur à 0.',
            ],
        );

        $this->coError = null;
        $this->coStep  = 'confirm';
    }

    public function submitCashOut(OperationsApi $operations): void
    {
        $this->coError = null;

        try {
            $result = $operations->processCashOut([
                'merchantId' => $this->coMerchantId,
                'amount'     => (int) $this->coAmount,
            ]);
        } catch (ApiException $exception) {
            $this->showApiError($exception, 'coError');

            return;
        }

        if (isset($result['status']) && $result['status'] === 202) {
            $this->coStatus = 202;
            $this->coResult = $result['data'];
        } else {
            $this->coStatus = 200;
            $this->coResult = $result;
        }

        $this->coStep = 'success';
    }

    public function resetCashOut(): void
    {
        $this->coStep = 'form';
        $this->coMerchantId = '';
        $this->coAmount = '';
        $this->coResult = null;
        $this->coError = null;
        $this->coStatus = 200;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.agent.operations');
    }
}
