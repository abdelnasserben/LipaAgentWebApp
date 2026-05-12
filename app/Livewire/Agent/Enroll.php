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
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Enrôler un client')]
class Enroll extends Component
{
    use HandlesApiErrors;
    use WithFileUploads;

    public int $step = 1;

    public ?string $apiError = null;

    // ── Step 1: Identity ───────────────────────────────────────────
    public string $fullName        = '';
    public string $dateOfBirth     = '';
    public string $phoneCountryCode = '269';
    public string $phoneNumber     = '';
    public string $nationalIdNumber = '';
    public string $nationalIdType  = 'NATIONAL_ID';

    // ── Step 2: Address (optional) ─────────────────────────────────
    public string $addressIsland   = '';
    public string $addressCity     = '';
    public string $addressDistrict = '';

    // ── Step 3: KYC docs ───────────────────────────────────────────
    /** @var array<int, array{type: string, fileKey: string}> */
    public array $kycDocuments = [];
    public string $kycDocType  = 'NATIONAL_ID';
    public $kycFile = null;

    // ── Step 4: Summary + card sale ────────────────────────────────
    public ?array $enrollResult     = null;
    public bool $offerCardSale      = false;
    public bool $cardSaleSelected   = false;
    public ?string $selectedNfcUid  = null;
    public array $cardStock         = [];
    public string $cardPrice        = '';
    public ?array $cardSaleResult   = null;
    public ?string $cardSaleError   = null;

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
            'fullName'         => ['required', 'max:255'],
            'dateOfBirth'      => ['required', 'date', 'before:today'],
            'phoneNumber'      => ['required', 'digits_between:4,15'],
            'nationalIdNumber' => ['required', 'max:100'],
            'nationalIdType'   => ['required', 'max:50'],
        ], [
            'fullName.required'         => 'Le nom complet est obligatoire.',
            'fullName.max'              => 'Le nom ne peut pas dépasser 255 caractères.',
            'dateOfBirth.required'      => 'La date de naissance est obligatoire.',
            'dateOfBirth.date'          => 'Date invalide.',
            'dateOfBirth.before'        => 'La date de naissance doit être dans le passé.',
            'phoneNumber.required'      => 'Le numéro de téléphone est obligatoire.',
            'phoneNumber.digits_between' => 'Le numéro doit comporter entre 4 et 15 chiffres.',
            'nationalIdNumber.required' => "Le numéro de pièce d'identité est obligatoire.",
            'nationalIdType.required'   => 'Le type de document est obligatoire.',
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
                'nationalIdType'   => $this->nationalIdType,
                'addressIsland'    => $this->addressIsland ?: null,
                'addressCity'      => $this->addressCity ?: null,
                'addressDistrict'  => $this->addressDistrict ?: null,
            ]);

            $customerId = (string) $this->enrollResult['customerId'];

            foreach ($this->kycDocuments as $doc) {
                $file = data_get($this, 'kycDocumentFiles.'.$doc['fileKey']);
                if ($file === null) {
                    continue;
                }
                $enroll->uploadKycDocument($customerId, $doc['type'], $file);
            }

            $agentProfile        = $agent->getProfile();
            $this->offerCardSale = (bool) ($agentProfile['canSellCards'] ?? false);

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

    /** @var array<string, \Illuminate\Http\UploadedFile> */
    public array $kycDocumentFiles = [];

    public function addKycDoc(): void
    {
        $this->validate(
            ['kycFile' => 'required|file|max:10240'],
            [
                'kycFile.required' => 'Sélectionnez un fichier à téléverser.',
                'kycFile.file'     => 'Fichier invalide.',
                'kycFile.max'      => 'Le fichier doit faire moins de 10 Mo.',
            ],
        );

        $key = uniqid('kyc_', true);
        $this->kycDocumentFiles[$key] = $this->kycFile;
        $this->kycDocuments[] = [
            'type'    => $this->kycDocType,
            'fileKey' => $key,
            'name'    => $this->kycFile->getClientOriginalName(),
        ];

        $this->kycFile = null;
    }

    public function removeKycDoc(int $index): void
    {
        $doc = $this->kycDocuments[$index] ?? null;
        if ($doc !== null) {
            unset($this->kycDocumentFiles[$doc['fileKey']]);
        }

        array_splice($this->kycDocuments, $index, 1);
        $this->kycDocuments = array_values($this->kycDocuments);
    }

    public function selectCard(string $nfcUid): void
    {
        $this->selectedNfcUid = $nfcUid;
        $this->cardSaleError  = null;
    }

    public function submitCardSale(CardApi $cards): void
    {
        $this->cardSaleError = null;

        $this->validate(
            [
                'selectedNfcUid' => 'required',
                'cardPrice'      => 'required|integer|min:1',
            ],
            [
                'selectedNfcUid.required' => 'Sélectionnez une carte.',
                'cardPrice.required'      => 'Le prix de la carte est requis.',
                'cardPrice.integer'       => 'Prix invalide.',
                'cardPrice.min'           => 'Le prix doit être supérieur à 0.',
            ],
        );

        if (! is_array($this->enrollResult) || empty($this->enrollResult['customerId'])) {
            $this->cardSaleError = 'Client non enrôlé.';
            return;
        }

        try {
            $this->cardSaleResult = $cards->sellCard([
                'customerId' => (string) $this->enrollResult['customerId'],
                'nfcUid'     => (string) $this->selectedNfcUid,
                'cardPrice'  => (int) $this->cardPrice,
            ]);
        } catch (ApiException $exception) {
            $this->showApiError($exception, 'cardSaleError');
            return;
        }

        $this->cardSaleSelected = true;
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
