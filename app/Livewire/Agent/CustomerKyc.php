<?php

declare(strict_types=1);

namespace App\Livewire\Agent;

use App\Contracts\Api\EnrollApi;
use App\Contracts\Api\OperationsApi;
use App\Exceptions\ApiException;
use App\Livewire\Concerns\HandlesApiErrors;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('KYC client')]
class CustomerKyc extends Component
{
    use HandlesApiErrors;
    use WithFileUploads;

    public ?string $apiError = null;

    public string $step = 'lookup'; // lookup | manage

    // Lookup state.
    public string $phoneCountryCode = '269';
    public string $phoneNumber      = '';
    public ?array $customer         = null;
    public ?string $lookupError     = null;

    // KYC docs state.
    /** @var array<int, array<string, mixed>> */
    public array $documents = [];
    public string $kycDocType = 'NATIONAL_ID';
    public $kycFile = null;
    public ?string $uploadError   = null;
    public ?string $uploadSuccess = null;

    public function lookupCustomer(OperationsApi $operations): void
    {
        $this->validate([
            'phoneNumber' => 'required|regex:/^\d{4,15}$/',
        ], [
            'phoneNumber.required' => 'Le numéro est requis.',
            'phoneNumber.regex'    => 'Numéro invalide (4 à 15 chiffres).',
        ]);

        $this->lookupError = null;

        try {
            $customer = $operations->lookupCustomer($this->phoneCountryCode, $this->phoneNumber);
        } catch (ApiException $exception) {
            $this->showApiError($exception, 'lookupError');
            return;
        }

        if (! $customer) {
            $this->lookupError = 'Client introuvable pour ce numéro.';
            return;
        }

        $this->customer = $customer;
        $this->step = 'manage';
        $this->loadDocuments(app(EnrollApi::class));
    }

    public function backToLookup(): void
    {
        $this->step = 'lookup';
        $this->customer = null;
        $this->documents = [];
        $this->kycFile = null;
        $this->uploadError = null;
        $this->uploadSuccess = null;
        $this->resetValidation();
    }

    public function uploadDocument(EnrollApi $enroll): void
    {
        $this->validate(
            [
                'kycDocType' => 'required|string',
                'kycFile'    => 'required|file|max:10240',
            ],
            [
                'kycDocType.required' => 'Sélectionnez un type de document.',
                'kycFile.required'    => 'Sélectionnez un fichier à téléverser.',
                'kycFile.file'        => 'Fichier invalide.',
                'kycFile.max'         => 'Le fichier doit faire moins de 10 Mo.',
            ],
        );

        if (! is_array($this->customer) || empty($this->customer['customerId'])) {
            $this->uploadError = 'Aucun client sélectionné.';
            return;
        }

        $this->uploadError = null;
        $this->uploadSuccess = null;

        try {
            $enroll->uploadKycDocument(
                (string) $this->customer['customerId'],
                $this->kycDocType,
                $this->kycFile,
            );
        } catch (ApiException $exception) {
            $this->showApiError($exception, 'uploadError');
            return;
        }

        $this->uploadSuccess = 'Document téléversé avec succès.';
        $this->kycFile = null;
        $this->loadDocuments($enroll);
    }

    private function loadDocuments(EnrollApi $enroll): void
    {
        if (! is_array($this->customer) || empty($this->customer['customerId'])) {
            $this->documents = [];
            return;
        }

        try {
            $this->documents = $enroll->listKycDocuments((string) $this->customer['customerId']);
        } catch (ApiException $exception) {
            $this->showApiError($exception);
            $this->documents = [];
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.agent.customer-kyc');
    }
}
