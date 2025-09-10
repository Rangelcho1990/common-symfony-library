<?php

declare(strict_types=1);

namespace CSL\Exceptions;

class NotImplementedException extends CslAbstractException
{
    /** @var int */
    protected $code = 501;

    /** @var string */
    protected $message = 'Not Implemented';
}
