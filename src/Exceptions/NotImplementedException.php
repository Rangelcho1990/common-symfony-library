<?php

declare(strict_types=1);

namespace CSL\Exceptions;

class NotImplementedException extends CslAbstractException
{
    /** @var int $code */
    protected $code = 501;

    /** @var string $message */
    protected $message = 'Not Implemented';
}
