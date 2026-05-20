<?php

namespace App\Exceptions;

use Exception;

class OllamaException extends Exception
{
    public function __construct(
        string $message,
        public readonly ?string $model = null,
        public readonly ?int $httpStatus = null,
    ) {
        parent::__construct($message);
    }
}
