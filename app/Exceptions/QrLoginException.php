<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Kegagalan pada alur login QR.
 *
 * `reason` dipakai controller untuk memetakan ke status HTTP + pesan yang
 * tepat (tidak ditemukan / kedaluwarsa / sudah dipakai / ditolak).
 */
class QrLoginException extends RuntimeException
{
    public const NOT_FOUND = 'not_found';

    public const EXPIRED = 'expired';

    public const USED = 'used';

    public const REJECTED = 'rejected';

    public const NOT_APPROVED = 'not_approved';

    public const NO_APPROVER = 'no_approver';

    public function __construct(public readonly string $reason, string $message = '')
    {
        parent::__construct($message !== '' ? $message : $reason);
    }
}
