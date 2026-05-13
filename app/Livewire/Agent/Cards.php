<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Contracts\Api\AgentApi;
use App\Contracts\Api\CardApi;
use App\Contracts\Api\OperationsApi;
use App\Exceptions\ApiException;
use App\Livewire\Concerns\HandlesApiErrors;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cartes')]
class Cards extends Component
{
    use HandlesApiErrors;

    public string $activeTab = 'sell'; // sell | report | replace

    // Report-card status: 'lost' or 'stolen' (chosen inside the unified report tab).
    public string $reportStatus = 'lost';

    public ?string $apiError = null;

    // Shared form state (customer + card identifiers).
    public string $customerId = '';
    public string $cardId     = '';

    // Replace-only state.
    public string $stockId        = '';
    public string $replacementFee = '';

    // Sell-card-outside-enrollment state.
    public string $sellStep              = 'lookup'; // lookup | select | success
    public string $sellPhoneCountryCode  = '269';
    public string $sellPhoneNumber       = '';
    public ?array $sellCustomer          = null;
    public string $sellNfcUid            = '';
    public string $sellCardPrice         = '';
    public ?array $sellResult            = null;
    public ?string $sellError            = null;

    /** @var array<int, array<string, mixed>> */
    public array $cardStock = [];

    /** @var array<string, mixed>|null */
    public ?array $lastResult = null;

    public bool $canSellCards = false;

    public function mount(AgentApi $agent, CardApi $cards): void
    {
        try {
            $profile = $agent->getProfile();
            $this->canSellCards = (bool) ($profile['canSellCards'] ?? false);

            if ($this->canSellCards) {
                $this->cardStock = $cards->getCardStock();
            }
        } catch (ApiException $exception) {
            $this->showApiError($exception);
        }
    }

    public function switchTab(string $tab): void
    {
        if (! in_array($tab, ['sell', 'report', 'replace'], true)) {
            return;
        }

        $this->activeTab  = $tab;
        $this->lastResult = null;
        $this->clearApiError();
        $this->resetValidation();
    }

    public function setReportStatus(string $status): void
    {
        if (! in_array($status, ['lost', 'stolen'], true)) {
            return;
        }

        $this->reportStatus = $status;
        $this->lastResult = null;
        $this->clearApiError();
        $this->resetValidation();
    }

    // ── Sell card outside enrollment ─────────────────────────

    public function lookupSellCustomer(OperationsApi $operations): void
    {
        $this->validate([
            'sellPhoneNumber' => 'required|regex:/^\d{4,15}$/',
        ], [
            'sellPhoneNumber.required' => 'Le numéro est requis.',
            'sellPhoneNumber.regex'    => 'Numéro invalide (4 à 15 chiffres).',
        ]);

        $this->sellError = null;

        try {
            $customer = $operations->lookupCustomer($this->sellPhoneCountryCode, $this->sellPhoneNumber);
        } catch (ApiException $exception) {
            $this->showApiError($exception, 'sellError');
            return;
        }

        if (! $customer) {
            $this->sellError = 'Client introuvable pour ce numéro.';
            return;
        }

        if (($customer['status'] ?? null) === 'SUSPENDED' || ($customer['status'] ?? null) === 'CLOSED') {
            $this->sellError = 'Ce compte client n’est pas actif.';
            return;
        }

        $this->sellCustomer = $customer;
        $this->sellStep = 'select';
    }

    public function backToSellLookup(): void
    {
        $this->sellStep = 'lookup';
        $this->sellCustomer = null;
        $this->sellNfcUid = '';
        $this->sellCardPrice = '';
        $this->sellError = null;
        $this->resetValidation();
    }

    public function selectSellCard(string $nfcUid): void
    {
        $this->sellNfcUid = $nfcUid;
        $this->sellError = null;
    }

    public function submitSell(CardApi $cards): void
    {
        $this->validate(
            [
                'sellNfcUid'   => 'required',
                'sellCardPrice' => 'required|integer|min:1',
            ],
            [
                'sellNfcUid.required'    => 'Sélectionnez une carte.',
                'sellCardPrice.required' => 'Le prix de la carte est requis.',
                'sellCardPrice.integer'  => 'Prix invalide.',
                'sellCardPrice.min'      => 'Le prix doit être supérieur à 0.',
            ],
        );

        if (! is_array($this->sellCustomer) || empty($this->sellCustomer['customerId'])) {
            $this->sellError = 'Client non sélectionné.';
            return;
        }

        $this->sellError = null;

        try {
            $this->sellResult = $cards->sellCard([
                'customerId' => (string) $this->sellCustomer['customerId'],
                'nfcUid'     => $this->sellNfcUid,
                'cardPrice'  => (int) $this->sellCardPrice,
            ]);
        } catch (ApiException $exception) {
            $this->showApiError($exception, 'sellError');
            return;
        }

        // Refresh stock so the just-sold card disappears from the list.
        try {
            $this->cardStock = app(CardApi::class)->getCardStock();
        } catch (ApiException) {
            // Non-fatal — keep the current list.
        }

        $this->sellStep = 'success';
    }

    public function resetSell(): void
    {
        $this->sellStep = 'lookup';
        $this->sellPhoneNumber = '';
        $this->sellCustomer = null;
        $this->sellNfcUid = '';
        $this->sellCardPrice = '';
        $this->sellResult = null;
        $this->sellError = null;
        $this->resetValidation();
    }

    public function reportCard(CardApi $cards): void
    {
        $this->validateIdentifiers();
        $this->clearApiError();

        try {
            $this->lastResult = $this->reportStatus === 'stolen'
                ? $cards->reportStolen($this->customerId, $this->cardId)
                : $cards->reportLost($this->customerId, $this->cardId);
        } catch (ApiException $exception) {
            $this->showApiError($exception);
        }
    }

    public function replaceCard(CardApi $cards): void
    {
        $this->validate(
            [
                'customerId'     => 'required|uuid',
                'cardId'         => 'required|uuid',
                'stockId'        => 'required|uuid',
                'replacementFee' => 'required|integer|min:1',
            ],
            [
                'customerId.required'     => "L'identifiant client est requis.",
                'customerId.uuid'         => "L'identifiant client doit être un UUID.",
                'cardId.required'         => "L'identifiant carte est requis.",
                'cardId.uuid'             => "L'identifiant carte doit être un UUID.",
                'stockId.required'        => 'Sélectionnez une carte de stock.',
                'stockId.uuid'            => "L'identifiant de stock doit être un UUID.",
                'replacementFee.required' => 'Le montant du frais de remplacement est requis.',
                'replacementFee.integer'  => 'Frais invalide.',
                'replacementFee.min'      => 'Le frais doit être supérieur à 0.',
            ],
        );

        $this->clearApiError();

        try {
            $this->lastResult = $cards->replaceCard($this->customerId, $this->cardId, [
                'stockId'        => $this->stockId,
                'replacementFee' => (int) $this->replacementFee,
            ]);
        } catch (ApiException $exception) {
            $this->showApiError($exception);
        }
    }

    public function resetForm(): void
    {
        $this->lastResult     = null;
        $this->customerId     = '';
        $this->cardId         = '';
        $this->stockId        = '';
        $this->replacementFee = '';
        $this->clearApiError();
        $this->resetValidation();
    }

    private function validateIdentifiers(): void
    {
        $this->validate(
            [
                'customerId' => 'required|uuid',
                'cardId'     => 'required|uuid',
            ],
            [
                'customerId.required' => "L'identifiant client est requis.",
                'customerId.uuid'     => "L'identifiant client doit être un UUID.",
                'cardId.required'     => "L'identifiant carte est requis.",
                'cardId.uuid'         => "L'identifiant carte doit être un UUID.",
            ],
        );
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.agent.cards');
    }
}
