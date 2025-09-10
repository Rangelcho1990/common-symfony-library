<?php

declare(strict_types=1);

namespace CSL\Exceptions;

class BadRequestException extends CslAbstractException
{
    /** @var int */
    protected $code = 400;

    /** @var string */
    protected $message = 'Bad Request';
}
