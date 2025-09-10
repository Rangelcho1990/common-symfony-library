<?php

declare(strict_types=1);

namespace CSL\Exceptions;

class ServiceUnavailableException extends CslAbstractException
{
    /** @var int $code */
    protected $code = 503;

    /** @var string $message */
    protected $message = 'Service Unavailable';
}
