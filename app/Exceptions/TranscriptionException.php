<?php

namespace App\Exceptions;

use Exception;

class TranscriptionException extends Exception
{
    public function __construct(
        string $message,
        public string $provider,
        public ?array $details = null,
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function render()
    {
        return response()->json([
            'error' => 'transcription_failed',
            'message' => $this->getMessage(),
            'provider' => $this->provider,
            'details' => $this->details,
        ], 422);
    }
}
