<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\CslLogger;

interface CslLoggerInterface
{
    // TODO: remove the interface
    /**
     * @param array<string, mixed> $context
     */
    public function addContext(array $context): void;

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array;
}
