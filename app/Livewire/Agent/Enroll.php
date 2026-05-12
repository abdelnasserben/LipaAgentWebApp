<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Contracts\Api\AgentApi;
use App\Contracts\Api\CardApi;
use App\Contracts\Api\EnrollApi;
use App\Exceptions\ApiException;
use App\Livewire\Concerns\HandlesApiErrors;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Enrôler un client')]
class Enroll extends Component
{
    use HandlesApiErrors;

    public int $step = 1;

    public ?string $apiError = null;

    // ── Step 1: Identity ───────────────────────────────────────────
    public string $fullName        = '';
    public string $dateOfBirth     = '';
    public string $phoneCountryCode = '269';
    public string $phoneNumber     = '';
    public string $nationalIdNumber = '';
    public string $nationalIdType  = 'NATIONAL_ID'; // NATIONAL_ID | PASSPORT | OTHER

    // ── Step 2: Address (optional) ─────────────────────────────────
    public string $addressIsland   = '';
    public string $addressCity     = '';
    public string $addressDistrict = '';

    // ── Step 3: KYC docs ───────────────────────────────────────────
    public array $kycDocuments = [];
    public string $kycDocType  = 'NATIONAL_ID';

    // ── Step 4: Summary + card sale ────────────────────────────────
    public ?array $enrollResult     = null;
    public bool $offerCardSale      = false;
    public bool $cardSaleSelected   = false;
    public ?string $selectedNfcUid  = null;
    public array $cardStock         = [];

    // ── General ────────────────────────────────────────────────────
    public bool $loading  = false;

    public function goToStep(int $n): void
    {
        if ($n >= 1 && $n < $this->step) {
            $this->step = $n;
            $this->resetValidation();
        }
    }

    public function nextStep(EnrollApi $enroll, AgentApi $agent, CardApi $cards): void
    {
        $this->resetValidation();

        match ($this->step) {
            1 => $this->validateStep1(),
            2 => $this->step = 3,
            3 => $this->submitEnrollment($enroll, $agent, $cards),
            default => null,
        };
    }

    private function validateStep1(): void
    {
        $this->validate([
            'fullName'        => ['required', 'max:255'],
            'dateOfBirth'     => ['required', 'date', 'before:today'],
            'phoneNumber'     => ['required', 'digits_between:4,15'],
            'nationalIdNumber' => ['required'],
            'nationalIdType'  => ['required', 'in:NATIONAL_ID,PASSPORT,OTHER'],
        ], [
            'fullName.required'        => 'Le nom complet est obligatoire.',
            'fullName.max'             => 'Le nom ne peut pas dépasser 255 caractères.',
            'dateOfBirth.required'     => 'La date de naissance est obligatoire.',
            'dateOfBirth.date'         => 'Date invalide.',
            'dateOfBirth.before'       => 'La date de naissance doit être dans le passé.',
            'phoneNumber.required'     => 'Le numéro de téléphone est obligatoire.',
            'phoneNumber.digits_between' => 'Le numéro doit comporter entre 4 et 15 chiffres.',
            'nationalIdNumber.required' => 'Le numéro de pièce d\'identité est obligatoire.',
            'nationalIdType.required'  => 'Le type de document est obligatoire.',
            'nationalIdType.in'        => 'Type de document invalide.',
        ]);

        $this->step = 2;
    }

    public function submitEnrollment(EnrollApi $enroll, AgentApi $agent, CardApi $cards): void
    {
        $this->clearApiError();
        $this->loading = true;

        try {
            $this->enrollResult = $enroll->enrollCustomer([
                'fullName'         => $this->fullName,
                'dateOfBirth'      => $this->dateOfBirth,
                'phoneCountryCode' => $this->phoneCountryCode,
                'phoneNumber'      => $this->phoneNumber,
                'nationalIdNumber' => $this->nationalIdNumber,
                'nationalIdType'  => $this->nationalIdType,
                'addressIsland'    => $this->addressIsland ?: null,
                'addressCity'      => $this->addressCity ?: null,
                'addressDistrict'  => $this->addressDistrict ?: null,
                'kycDocuments'     => $this->kycDocuments,
            ]);

            // Upload any queued KYC documents
            if (! empty($this->kycDocuments)) {
                foreach ($this->kycDocuments as $doc) {
                    $enroll->uploadKycDocument($this->enrollResult['customerId'], [
                        'documentType' => $doc['type'],
                        'filename'     => $doc['filename'],
                    ]);
                }
            }

            // Check if agent can sell cards
            $agentProfile        = $agent->getProfile();
            $this->offerCardSale = $agentProfile['canSellCards'] ?? false;

            if ($this->offerCardSale) {
                $this->cardStock = $cards->getCardStock();
            }
        } catch (ApiException $exception) {
            $this->loading = false;
            $this->showApiError($exception);

            return;
        }

        $this->loading = false;
        $this->step    = 4;
    }

    public function addKycDoc(): void
    {
        $index               = count($this->kycDocuments) + 1;
        $this->kycDocuments[] = [
            'type'     => $this->kycDocType,
            'filename' => 'document_' . $index . '.jpg',
            'status'   => 'PENDING_REVIEW',
        ];
    }

    public function removeKycDoc(int $index): void
    {
        array_splice($this->kycDocuments, $index, 1);
        $this->kycDocuments = array_values($this->kycDocuments);
    }

    public function selectCard(string $nfcUid): void
    {
        $this->selectedNfcUid = $nfcUid;
    }

    public function finish(): void
    {
        $this->redirect(route('dashboard'), navigate: true);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.agent.enroll');
    }
}
