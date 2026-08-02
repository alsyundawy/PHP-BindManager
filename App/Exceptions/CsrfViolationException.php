<?php

declare(strict_types=1);

namespace App\Exceptions;

final class CsrfViolationException extends HttpException
{
    public function __construct(string $message = 'Invalid CSRF token.')
    {
        parent::__construct($message, 419);
    }
}
