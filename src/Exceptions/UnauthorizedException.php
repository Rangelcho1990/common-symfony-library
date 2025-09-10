<?php

declare(strict_types=1);

namespace CSL\Exceptions;

class UnauthorizedException extends CslAbstractException
{
    /** @var int $code */
    protected $code = 401;

    /** @var string $message */
    protected $message = 'Unauthorized';
}
