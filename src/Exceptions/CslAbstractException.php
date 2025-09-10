<?php

declare(strict_types=1);

namespace CSL\Exceptions;

use Doctrine\ORM\EntityManagerInterface;

class CslAbstractException extends \Exception
{
    /** @var string $message */
    protected $message = 'Unknown error';

    public function __construct(string $message = '', int $code = 0, \Exception $previous = null)
    {
        if(!empty($message)) {
            $this->message = $message;
        }

        parent::__construct($message, $code, $previous);
    }

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'code' => $this->code,
        ];
    }
}
