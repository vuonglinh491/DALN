<?php

namespace App\Services\Ai;

class GeminiException extends \RuntimeException
{
    /** @param string $reason config|timeout|quota|server|auth|blocked|empty|http */
    public function __construct(string $message, public string $reason = 'http')
    {
        parent::__construct($message);
    }
}
