<?php

declare(strict_types=1);

namespace CSL\Exceptions;

class ParameterNotFoundException extends CslAbstractException
{
    /** @var int */
    protected $code = 404;

    /** @var string */
    protected $message = 'Missing parameter';
}
