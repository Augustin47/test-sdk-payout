<?php

namespace App\SDK\payouts\V2\Sandbox\src;

class Utilities
{
    /**
     * @param string $phoneNumber
     * @return array
     * @description This method is used to simulate payment status according to a phone number
     */
    public static function phoneNumberStatus(string $phoneNumber): array
    {
        return match ($phoneNumber) {
            '+2250707000200' => [200, 200],
            '+2250707000201' => [103, 200],
            '+2250707000205' => [300, 300],
            '+2250707000202' => [103, 302],
            '+2250707000203' => [103, 305],
            '+2250707000204' => [103, 300],
            '+2250707000206' => [303, 303],
            default => [103, 300],
        };
    }

    public static function listStatusCode(): array
    {
        return [
            200 => ['state' => 'success', 'message' => 'SUCCESS', 'description' => 'Transaction effectuée avec succès'],
            100 => ['state' => 'pending', 'message' => 'NEW', 'description' => 'Votre transaction est en attente de traitement'],
            101 => ['state' => 'pending', 'message' => 'PENDING', 'description' => 'Votre transaction est en cours de traitement'],
            102 => ['state' => 'pending', 'message' => 'WAITING_CUSTOMER_PAYMENT', 'description' => 'Veuillez suivre les instructions pour valider votre paiement'],
            103 => ['state' => 'pending', 'message' => 'WAITING_CUSTOMER_CONFIRMATION', 'description' => 'Veuillez suivre les instructions pour valider votre paiement'],
            300 => ['state' => 'failed', 'message' => 'FAILED', 'description' => 'Votre transaction a echoué'],
            301 => ['state' => 'failed', 'message' => 'OTP_INVALID', 'description' => 'Le code OTP saisi est invalide'],
            302 => ['state' => 'failed', 'message' => 'BALANCE_INSUFFICIENT', 'description' => "Vous n'avez pas assez de fonds pour effectuer cette transaction"],
            303 => ['state' => 'failed', 'message' => 'PARTNER_UNAVAILABLE', 'description' => 'La plateforme du partenaire est indisponible'],
            304 => ['state' => 'failed', 'message' => 'TIMEOUT', 'description' => 'Votre transaction a echoué pour temps de traitement depassé'],
            305 => ['state' => 'failed', 'message' => 'LIMIT_REACHED', 'description' => 'Vous avez atteint la limite de transaction'],
            306 => ['state' => 'failed', 'message' => 'EXPIRED', 'description' => 'User has not confirmed the payment in time'],
            999 => ['state' => null, 'message' => 'NETWORK_ERROR', 'description' => 'Network error'],
        ];
    }
}
