<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle;

interface CslLoggerInterface
{
    public function addContext(array $context): void;

    public function getContext(): array;
}
