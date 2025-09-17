<?php

declare(strict_types=1);

namespace CSL\Exceptions;

class CslAbstractException extends \Exception
{
    /** @var string */
    protected $message = 'Unknown error';

    public function __construct(string $message = '', int $code = 0, ?\Exception $previous = null)
    {
        if (!empty($message)) {
            $this->message = $message;
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array<string, string|int>
     */
    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'code' => $this->code,
        ];
    }
}
