<?php

declare(strict_types=1);

namespace CSL\Exceptions;

class UnauthorizedException extends CslAbstractException
{
    /** @var int */
    protected $code = 401;

    /** @var string */
    protected $message = 'Unauthorized';
}
