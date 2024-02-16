<?php

namespace App\SDK\Cross_swift\Bank_transfer\src;

class Utilities
{
    public static function bankTransferStatus(string $status): string
    {
        return match ($status) {
            'CO_SUBMITTED' => 100,
            'CO_SERVER_ERROR' => 303,
            'CO_BAD_REQUEST' => 400,
            'CO_SUCCESS' => 200,
            'CO_UN_AUTHORIZED' => 401,
            'CO_PREPARED' => 202,
            'CO_INVALID_MERCHANT_REF' => 404,
            'CO_CONFLICTING' => 409,
            'CO_PROCESSING' => 101,
            'CO_EXPIRED' => 306,
            'CO_CANCELLED' => 410,
            'CO_PENDING' => 102,
            default => 999,
        };
    }

    public static function listStatusCode(): array
    {
        return [
            200 => ['state' => 'success', 'message' => 'SUCCESS', 'description' => 'Transaction effectuée avec succès'],
            202 => ['state' => 'pending', 'message' => 'PENDING', 'description' => 'Transaction submitted successfully but it\'s in progress for final status.'],
            100 => ['state' => 'pending', 'message' => 'NEW', 'description' => 'Votre transaction est en attente de traitement'],
            101 => ['state' => 'pending', 'message' => 'PENDING', 'description' => 'Votre transaction est en cours de traitement'],
            102 => ['state' => 'pending', 'message' => 'WAITING', 'description' => '  Transaction is awaiting further processing or confirmation beforeit is completed or finalized'],
            300 => ['state' => 'failed', 'message' => 'FAILED', 'description' => 'Votre transaction a echoué'],
            303 => ['state' => 'failed', 'message' => 'PARTNER_UNAVAILABLE', 'description' => 'La plateforme du partenaire est indisponible'],
            306 => ['state' => 'failed', 'message' => 'EXPIRED', 'description' => 'Transaction is no longer valid or eligible for further processing'],
            400 => ['state' => 'failed', 'message' => 'BAD_REQUEST', 'description' => 'Errors occur due to incorrect or invalid input.'],
            401 => ['state' => 'failed', 'message' => 'UNAUTHORIZED', 'description' => 'Invalid credentials'],
            404 => ['state' => 'failed', 'message' => 'NOT_FOUND', 'description' => 'Transaction ID not exist'],
            409 => ['state' => 'failed', 'message' => 'CONFLICTING', 'description' => 'Transaction encounters a conflict or inconsistency with an existing transaction.'],
            410 => ['state' => 'failed', 'message' => 'CANCELLED', 'description' => 'Transaction that has been terminated, resulting in its cancellation and no further processing.'],
            999 => ['state' => 'failed', 'message' => 'NETWORK_ERROR', 'description' => 'Network error'],
        ];
    }
}
