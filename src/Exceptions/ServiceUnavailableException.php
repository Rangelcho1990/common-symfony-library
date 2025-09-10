<?php

declare(strict_types=1);

namespace CSL\Exceptions;

class ServiceUnavailableException extends CslAbstractException
{
    /** @var int */
    protected $code = 503;

    /** @var string */
    protected $message = 'Service Unavailable';
}
