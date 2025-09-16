<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle;

interface CslLoggerInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function addContext(array $context): void;

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array;
}
