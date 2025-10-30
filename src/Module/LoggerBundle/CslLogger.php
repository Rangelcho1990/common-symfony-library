<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle;

use Psr\Log\LoggerInterface;

final class CslLogger implements CslLoggerInterface
{
    /** @var array<string, mixed> */
    private array $context = [];

    public function __construct(public LoggerInterface $logger)
    {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function addContext(array $context): void
    {
        if (!empty($context)) {
            $this->context = array_merge($this->context, $context);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
