<?php

declare(strict_types=1);

namespace CSL\Exceptions;

class BadRequestException extends CslAbstractException
{
    /** @var int $code */
    protected $code = 400;

    /** @var string $message */
    protected $message = 'Bad Request';
}
