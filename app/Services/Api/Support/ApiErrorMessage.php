<?php

declare(strict_types=1);

namespace App\Services\Api\Support;

use App\Exceptions\ApiException;

final class ApiErrorMessage
{
    public static function fromException(ApiException $exception): string
    {
        $message = self::forCode($exception->apiCode(), $exception->getMessage());
        $details = self::detailsText($exception->details());

        if ($details !== '' && str_starts_with($exception->apiCode(), 'VALIDATION')) {
            return $message.' '.$details;
        }

        return $message;
    }

    public static function forCode(string $apiCode, string $apiMessage = ''): string
    {
        $messages = [
            'INVALID_CREDENTIALS' => 'Identifiants invalides. Verifiez le telephone et le PIN.',
            'AUTH_PIN_NOT_SET' => "Aucun PIN Agent n'est configure pour ce compte.",
            'AUTH_PIN_LOCKED' => 'PIN Agent verrouille apres trop de tentatives. Reessayez dans 15 minutes.',
            'AUTH_PIN_ALREADY_SET' => 'Un PIN Agent est deja configure pour ce compte.',
            'AUTH_PIN_FORMAT' => 'Le format du PIN Agent est invalide.',
            'AUTH_PIN_INVALID' => 'PIN Agent invalide.',
            'AUTH_INVALID_TOKEN' => 'Lien de definition de PIN expire ou invalide. Reconnectez-vous pour recommencer.',
            'MFA_REQUIRED' => 'Code TOTP requis pour terminer la connexion.',
            'MFA_INVALID' => 'Code TOTP invalide ou expire.',
            'LEGACY_OTP_LOGIN_REMOVED' => "L'ancien login SMS OTP n'est plus disponible. Utilisez telephone et PIN.",
            'REFRESH_TOKEN_INVALID' => 'Votre session a expire. Reconnectez-vous.',

            'UNAUTHORIZED' => 'Votre session a expire. Reconnectez-vous.',
            'TOKEN_EXPIRED' => 'Votre session a expire. Reconnectez-vous.',
            'TOKEN_REVOKED' => 'Votre session a ete revoquee. Reconnectez-vous.',
            'FORBIDDEN' => "Vous n'avez pas les droits pour effectuer cette action.",

            'VALIDATION_FIELD_REQUIRED' => 'Tous les champs obligatoires doivent etre renseignes.',
            'VALIDATION_ERROR' => 'Certaines donnees envoyees sont invalides.',
            'VALIDATION_INVALID_FORMAT' => 'Le format de certains champs est invalide.',
            'TERMINAL_RATE_LIMIT' => 'Trop de tentatives. Reessayez dans quelques instants.',

            'ACTOR_PENDING_KYC' => 'Compte Agent en attente de validation KYC.',
            'ACTOR_SUSPENDED' => 'Compte Agent suspendu. Contactez le support Lipa.',
            'ACTOR_CLOSED' => 'Compte Agent ferme. Contactez le support Lipa.',
            'ACTOR_NOT_FOUND' => 'Compte Agent introuvable.',
            'AGENT_NOT_FOUND' => 'Agent introuvable.',

            'WALLET_NOT_FOUND' => 'Wallet introuvable.',
            'WALLET_FROZEN' => 'Wallet gele. Operation impossible.',
            'WALLET_SUSPENDED' => 'Wallet suspendu. Operation impossible.',
            'WALLET_CLOSED' => 'Wallet ferme. Operation impossible.',
            'INSUFFICIENT_BALANCE' => 'Solde insuffisant pour effectuer cette operation.',

            'CONFIG_LIMIT_PROFILE_NOT_FOUND' => "Aucun profil de limites n'est configure pour cet Agent.",
            'CONFIG_RULE_INACTIVE' => 'La regle de limite est inactive.',
            'LIMIT_EXCEEDED' => 'Limite autorisee depassee pour cette operation.',

            'CUSTOMER_NOT_FOUND' => 'Client introuvable.',
            'MERCHANT_NOT_FOUND' => 'Marchand introuvable.',
            'TRANSACTION_NOT_FOUND' => 'Transaction introuvable.',
            'PHONE_ALREADY_IN_USE' => 'Ce numero de telephone est deja utilise.',
            'NATIONAL_ID_ALREADY_IN_USE' => "Ce numero de piece d'identite est deja utilise.",

            'CASH_OUT_NOT_ALLOWED' => 'Cash-out non autorise pour ce marchand.',
            'APPROVAL_REQUIRED' => 'Cette operation necessite une approbation.',
            'DUPLICATE_IDEMPOTENCY_KEY' => 'Cette operation est deja en cours. Patientez puis actualisez.',

            'CARD_STOCK_NOT_FOUND' => 'Carte NFC introuvable dans le stock Agent.',
            'CARD_STOCK_WRONG_STATUS' => "Cette carte NFC n'est pas disponible.",
            'CARD_STOCK_NOT_ASSIGNED_TO_AGENT' => "Cette carte NFC n'est pas assignee a cet Agent.",
            'CARD_NOT_FOUND' => 'Carte introuvable.',
            'CARD_NOT_ACTIVE' => "La carte selectionnee n'est pas active.",

            'AUTH_ENDPOINT_NOT_FOUND' => "Endpoint de login Agent introuvable sur l'API configuree.",
            'INVALID_RESPONSE' => "Reponse API incomplete ou invalide.",
            'API_CONNECTION_FAILED' => "Impossible de joindre l'API. Verifiez que le backend est demarre.",
            'API_ERROR' => 'Operation impossible pour le moment.',
            'NOT_FOUND' => 'Ressource introuvable.',
            'METHOD_NOT_ALLOWED' => 'Endpoint API appele avec une methode non autorisee.',
            'BUSINESS_RULE_FAILED' => 'Operation refusee par une regle metier.',
            'API_SERVER_ERROR' => 'Erreur interne cote API. Reessayez plus tard.',
        ];

        if (isset($messages[$apiCode])) {
            return $messages[$apiCode];
        }

        if ($apiMessage !== '' && $apiMessage !== $apiCode) {
            return $apiMessage;
        }

        return 'Operation impossible pour le moment.';
    }

    /**
     * @param  array<int, mixed>  $details
     */
    private static function detailsText(array $details): string
    {
        $messages = [];

        foreach ($details as $detail) {
            if (is_string($detail)) {
                $messages[] = $detail;
                continue;
            }

            if (is_array($detail)) {
                $field = is_string($detail['field'] ?? null) ? $detail['field'] : null;
                $message = is_string($detail['message'] ?? null) ? $detail['message'] : null;

                if ($field !== null && $message !== null) {
                    $messages[] = "{$field}: {$message}";
                } elseif ($message !== null) {
                    $messages[] = $message;
                }
            }
        }

        $messages = array_slice(array_filter($messages), 0, 3);

        return $messages === [] ? '' : 'Details: '.implode(' ', $messages);
    }
}
