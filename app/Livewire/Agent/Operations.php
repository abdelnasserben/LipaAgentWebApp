<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Contracts\Api\OperationsApi;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Opérations')]
class Operations extends Component
{
    public string $activeTab = 'cash-in';

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
    public string $coMerchantRef = '';
    public string $coAmount = '';
    public ?array $coMerchant = null;
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
        $customer = $operations->lookupCustomer($this->ciPhoneCountryCode, $this->ciPhoneNumber);

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
        $result = $operations->processCashIn([
            'customerId' => $this->ciCustomer['customerId'],
            'amount'     => (int) $this->ciAmount,
        ]);

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

    public function lookupMerchant(): void
    {
        $this->validate([
            'coMerchantRef' => 'required|min:3',
            'coAmount'      => 'required|integer|min:1',
        ], [
            'coMerchantRef.required' => 'La référence marchand est requise.',
            'coMerchantRef.min'      => 'Référence trop courte.',
            'coAmount.required'      => 'Le montant est requis.',
            'coAmount.integer'       => 'Montant invalide.',
            'coAmount.min'           => 'Le montant doit être supérieur à 0.',
        ]);

        $this->coError = null;

        if (str_ends_with(strtoupper($this->coMerchantRef), '-003')) {
            $this->coError = 'Ce marchand est suspendu.';
            return;
        }

        $this->coMerchant = [
            'merchantId'   => Str::uuid()->toString(),
            'businessName' => 'Commerce ' . strtoupper($this->coMerchantRef),
            'status'       => 'ACTIVE',
            'kycLevel'     => 'KYC_VERIFIED',
        ];

        $this->coStep = 'confirm';
    }

    public function submitCashOut(OperationsApi $operations): void
    {
        $this->coError = null;

        $result = $operations->processCashOut([
            'merchantId' => $this->coMerchant['merchantId'],
            'amount'     => (int) $this->coAmount,
        ]);

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
        $this->coMerchantRef = '';
        $this->coAmount = '';
        $this->coMerchant = null;
        $this->coResult = null;
        $this->coError = null;
        $this->coStatus = 200;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.agent.operations');
    }
}
