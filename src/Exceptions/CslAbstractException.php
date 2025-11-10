<?php

declare(strict_types=1);

namespace CSL\Exceptions;

class CslAbstractException extends \Exception
{
    /** @var string */
    protected $message = 'Unknown error';

    /** @var int */
    protected $code = 0;

    public function __construct(string $message = '', int $code = 0, ?\Exception $previous = null)
    {
        $finalMessage = !empty($message) ? $message : $this->message;
        $finalCode = 0 !== $code ? $code : $this->code;

        parent::__construct($finalMessage, $finalCode, $previous);
    }

    /**
     * @return array{message: string, code: int}
     */
    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'code' => $this->code,
        ];
    }
}
