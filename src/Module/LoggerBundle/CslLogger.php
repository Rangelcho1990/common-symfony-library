<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle;

use Psr\Log\LoggerInterface;

final class CslLogger implements CslLoggerInterface
{
    public LoggerInterface $logger;

    private array $context = [];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function addContext(array $context): void
    {
        if (!empty($context)) {
            $this->context = array_merge($this->context, $context);
        }
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
