<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Contracts\Api\OperationsApi;
use App\Exceptions\ApiException;
use App\Livewire\Concerns\HandlesApiErrors;
use Illuminate\Support\Str;
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
    public string $coStep = 'lookup'; // lookup | amount | confirm | pin | confirmation | success
    public string $coPhoneCountryCode = '269';
    public string $coPhoneNumber = '';
    public ?array $coMerchant = null;
    public string $coAmount = '';
    public ?array $coResult = null;
    public ?string $coError = null;
    public int $coStatus = 200;
    public string $coOutcome = '';                // EXECUTED | PENDING_PIN | PENDING_CONFIRMATION | PENDING_APPROVAL
    public ?string $coIdempotencyKey = null;      // Stable across PENDING_* resubmissions
    public ?int $coMatchedThreshold = null;       // Returned with PENDING_PIN / PENDING_CONFIRMATION
    public string $coMerchantPin = '';            // NEVER logged; cleared right after the API call
    public ?string $coPinError = null;

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

    public function lookupMerchant(OperationsApi $operations): void
    {
        $this->validate([
            'coPhoneNumber' => 'required|regex:/^\d{4,15}$/',
        ], [
            'coPhoneNumber.required' => 'Le numéro est requis.',
            'coPhoneNumber.regex'    => 'Numéro invalide (4 à 15 chiffres).',
        ]);

        $this->coError = null;

        try {
            $merchant = $operations->lookupMerchant($this->coPhoneCountryCode, $this->coPhoneNumber);
        } catch (ApiException $exception) {
            $this->showApiError($exception, 'coError');

            return;
        }

        if (! $merchant) {
            $this->coError = 'Marchand introuvable pour ce numéro.';
            return;
        }

        $status = (string) ($merchant['status'] ?? '');
        if (in_array($status, ['SUSPENDED', 'CLOSED', 'PENDING_KYC'], true)) {
            $this->coError = 'Ce marchand n’est pas actif.';
            return;
        }

        if (array_key_exists('canCashOut', $merchant) && $merchant['canCashOut'] === false) {
            $this->coError = 'Le cash-out n’est pas autorisé pour ce marchand.';
            return;
        }

        $this->coMerchant = $merchant;
        $this->coStep = 'amount';
    }

    public function backToMerchantLookup(): void
    {
        $this->coStep = 'lookup';
        $this->coMerchant = null;
        $this->coError = null;
        $this->resetValidation();
    }

    public function setCashOutAmount(int $amount): void
    {
        $this->coAmount = (string) $amount;
    }

    public function confirmCashOut(): void
    {
        if (! is_array($this->coMerchant) || empty($this->coMerchant['merchantId'])) {
            $this->coError = 'Sélectionnez un marchand.';
            $this->coStep = 'lookup';
            return;
        }

        $this->validate(
            [
                'coAmount' => 'required|integer|min:1',
            ],
            [
                'coAmount.required' => 'Le montant est requis.',
                'coAmount.integer'  => 'Montant invalide.',
                'coAmount.min'      => 'Le montant doit être supérieur à 0.',
            ],
        );

        $this->coError = null;
        $this->coStep  = 'confirm';
    }

    public function backToAmount(): void
    {
        $this->coStep = 'amount';
        $this->coError = null;
    }

    public function submitCashOut(OperationsApi $operations): void
    {
        if (! is_array($this->coMerchant) || empty($this->coMerchant['merchantId'])) {
            $this->coError = 'Sélectionnez un marchand.';
            $this->coStep = 'lookup';
            return;
        }

        if ($this->coIdempotencyKey === null) {
            $this->coIdempotencyKey = (string) Str::uuid();
        }

        $this->coError = null;

        $this->sendCashOut($operations, [
            'merchantId' => (string) $this->coMerchant['merchantId'],
            'amount'     => (int) $this->coAmount,
        ]);
    }

    public function submitMerchantPin(OperationsApi $operations): void
    {
        $this->validate([
            'coMerchantPin' => 'required|regex:/^\d{4,8}$/',
        ], [
            'coMerchantPin.required' => 'Le PIN marchand est requis.',
            'coMerchantPin.regex'    => 'Le PIN doit contenir 4 à 8 chiffres.',
        ]);

        if ($this->coIdempotencyKey === null || ! is_array($this->coMerchant)) {
            $this->resetCashOut();
            return;
        }

        // Capture-then-clear so the raw PIN is never persisted on the component
        // (Livewire serializes public props between requests).
        $pin = $this->coMerchantPin;
        $this->coMerchantPin = '';
        $this->coPinError = null;

        $this->sendCashOut($operations, [
            'merchantId'  => (string) $this->coMerchant['merchantId'],
            'amount'      => (int) $this->coAmount,
            'merchantPin' => $pin,
        ]);

        unset($pin);
    }

    public function acknowledgeConfirmation(OperationsApi $operations): void
    {
        if ($this->coIdempotencyKey === null || ! is_array($this->coMerchant)) {
            $this->resetCashOut();
            return;
        }

        $this->coError = null;

        $this->sendCashOut($operations, [
            'merchantId'               => (string) $this->coMerchant['merchantId'],
            'amount'                   => (int) $this->coAmount,
            'confirmationAcknowledged' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function sendCashOut(OperationsApi $operations, array $payload): void
    {
        try {
            $result = $operations->processCashOut($payload, (string) $this->coIdempotencyKey);
        } catch (ApiException $exception) {
            $this->handleCashOutException($exception, array_key_exists('merchantPin', $payload));

            return;
        }

        if (isset($result['status']) && $result['status'] === 202) {
            $this->coStatus = 202;
            $this->coResult = $result['data'];
        } else {
            $this->coStatus = 200;
            $this->coResult = $result;
        }

        $this->routeCashOutOutcome();
    }

    private function handleCashOutException(ApiException $exception, bool $wasPinSubmit): void
    {
        $code = $exception->apiCode();

        if ($wasPinSubmit && $code === 'AUTH_PIN_INVALID') {
            $this->coPinError = 'PIN marchand invalide. Demandez au marchand de réessayer.';
            $this->coStep = 'pin';
            return;
        }

        if ($wasPinSubmit && $code === 'AUTH_PIN_LOCKED') {
            $this->coPinError = 'PIN marchand verrouillé après 3 tentatives. Réessayez dans 15 minutes.';
            $this->coStep = 'pin';
            return;
        }

        $this->showApiError($exception, 'coError');
    }

    private function routeCashOutOutcome(): void
    {
        $outcome = is_array($this->coResult)
            ? (string) ($this->coResult['outcome'] ?? ($this->coResult['status'] ?? ''))
            : '';

        $this->coOutcome = $outcome;
        $this->coMatchedThreshold = is_array($this->coResult) && isset($this->coResult['matchedThresholdAmount'])
            ? (int) $this->coResult['matchedThresholdAmount']
            : null;

        $this->coStep = match ($outcome) {
            'PENDING_PIN'          => 'pin',
            'PENDING_CONFIRMATION' => 'confirmation',
            default                => 'success', // EXECUTED, PENDING_APPROVAL
        };
    }

    public function resetCashOut(): void
    {
        $this->coStep = 'lookup';
        $this->coPhoneNumber = '';
        $this->coMerchant = null;
        $this->coAmount = '';
        $this->coResult = null;
        $this->coError = null;
        $this->coStatus = 200;
        $this->coOutcome = '';
        $this->coIdempotencyKey = null;
        $this->coMatchedThreshold = null;
        $this->coMerchantPin = '';
        $this->coPinError = null;
        $this->resetValidation();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.agent.operations');
    }
}
