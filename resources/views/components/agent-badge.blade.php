@props(['status', 'label' => null])
@php
    $configs = [
        'ACTIVE'           => ['label' => 'Actif',          'color' => 'var(--green)',  'bg' => 'var(--green-bg)'],
        'SUSPENDED'        => ['label' => 'Suspendu',       'color' => 'var(--amber)',  'bg' => 'var(--amber-bg)'],
        'CLOSED'           => ['label' => 'Fermé',          'color' => 'var(--red)',    'bg' => 'var(--red-bg)'],
        'PENDING_KYC'      => ['label' => 'KYC Pending',    'color' => 'var(--blue)',   'bg' => 'var(--blue-bg)'],
        'FROZEN'           => ['label' => 'Gelé',           'color' => 'var(--amber)',  'bg' => 'var(--amber-bg)'],
        'KYC_NONE'         => ['label' => 'KYC None',       'color' => 'var(--text-secondary)', 'bg' => 'var(--border-color)'],
        'KYC_BASIC'        => ['label' => 'KYC Basic',      'color' => 'var(--blue)',   'bg' => 'var(--blue-bg)'],
        'KYC_VERIFIED'     => ['label' => 'KYC Verified',   'color' => 'var(--purple)', 'bg' => 'var(--purple-bg)'],
        'KYC_ENHANCED'     => ['label' => 'KYC Enhanced',   'color' => 'var(--green)',  'bg' => 'var(--green-bg)'],
        'COMPLETED'        => ['label' => 'Complété',       'color' => 'var(--green)',  'bg' => 'var(--green-bg)'],
        'PENDING'          => ['label' => 'En attente',     'color' => 'var(--blue)',   'bg' => 'var(--blue-bg)'],
        'PENDING_APPROVAL' => ['label' => 'Approbation',    'color' => 'var(--amber)',  'bg' => 'var(--amber-bg)'],
        'DECLINED'         => ['label' => 'Refusé',         'color' => 'var(--red)',    'bg' => 'var(--red-bg)'],
        'AUTHORIZED'       => ['label' => 'Autorisé',       'color' => 'var(--teal)',   'bg' => 'var(--teal-bg)'],
        'EXPIRED'          => ['label' => 'Expiré',         'color' => 'var(--amber)',  'bg' => 'var(--amber-bg)'],
        'REVERSED'         => ['label' => 'Annulé',         'color' => 'var(--indigo)', 'bg' => 'var(--indigo-bg)'],
        'CASH_IN'          => ['label' => 'Cash In',        'color' => 'var(--green)',  'bg' => 'var(--green-bg)'],
        'CASH_OUT'         => ['label' => 'Cash Out',       'color' => 'var(--amber)',  'bg' => 'var(--amber-bg)'],
        'CARD_SALE'        => ['label' => 'Vente carte',    'color' => 'var(--purple)', 'bg' => 'var(--purple-bg)'],
        'CARD_REPLACEMENT' => ['label' => 'Remplacement',   'color' => 'var(--blue)',   'bg' => 'var(--blue-bg)'],
        'STANDARD'         => ['label' => 'Standard',       'color' => 'var(--text-secondary)', 'bg' => 'var(--border-color)'],
        'PREMIUM'          => ['label' => 'Premium',        'color' => 'var(--purple)', 'bg' => 'var(--purple-bg)'],
        'CORPORATE'        => ['label' => 'Corporate',      'color' => 'var(--blue)',   'bg' => 'var(--blue-bg)'],
        'ISSUED'           => ['label' => 'Émise',          'color' => 'var(--blue)',   'bg' => 'var(--blue-bg)'],
        'BLOCKED'          => ['label' => 'Bloquée',        'color' => 'var(--amber)',  'bg' => 'var(--amber-bg)'],
        'LOST'             => ['label' => 'Perdue',         'color' => 'var(--amber)',  'bg' => 'var(--amber-bg)'],
        'STOLEN'           => ['label' => 'Volée',          'color' => 'var(--red)',    'bg' => 'var(--red-bg)'],
        'COMMISSION_PAYOUT'=> ['label' => 'Commission',     'color' => 'var(--teal)',   'bg' => 'var(--teal-bg)'],
        'AGENT_FUND_IN'    => ['label' => 'Approvisionnement', 'color' => 'var(--blue)', 'bg' => 'var(--blue-bg)'],
        'AGENT_FUND_OUT'   => ['label' => 'Retrait float',  'color' => 'var(--indigo)', 'bg' => 'var(--indigo-bg)'],
        'PAID'             => ['label' => 'Payé',           'color' => 'var(--green)',  'bg' => 'var(--green-bg)'],
        'FAILED'           => ['label' => 'Échoué',         'color' => 'var(--red)',    'bg' => 'var(--red-bg)'],
        'CANCELLED'        => ['label' => 'Annulé',         'color' => 'var(--red)',    'bg' => 'var(--red-bg)'],
        'PENDING_REVIEW'   => ['label' => 'En révision',    'color' => 'var(--amber)',  'bg' => 'var(--amber-bg)'],
        'ACCEPTED'         => ['label' => 'Accepté',        'color' => 'var(--green)',  'bg' => 'var(--green-bg)'],
        'REJECTED'         => ['label' => 'Refusé',         'color' => 'var(--red)',    'bg' => 'var(--red-bg)'],
        'DEBIT'            => ['label' => 'Débit',          'color' => 'var(--amber)',  'bg' => 'var(--amber-bg)'],
        'CREDIT'           => ['label' => 'Crédit',         'color' => 'var(--green)',  'bg' => 'var(--green-bg)'],
    ];
    $cfg = $configs[$status] ?? ['label' => $status ?? '—', 'color' => 'var(--text-secondary)', 'bg' => 'var(--border-color)'];
    $displayLabel = $label ?? $cfg['label'];
@endphp
<span style="display:inline-flex;align-items:center;gap:5px;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;letter-spacing:0.03em;text-transform:uppercase;color:{{ $cfg['color'] }};background:{{ $cfg['bg'] }};">
    <span style="width:5px;height:5px;border-radius:50%;background:{{ $cfg['color'] }};flex-shrink:0;"></span>
    {{ $displayLabel }}
</span>
