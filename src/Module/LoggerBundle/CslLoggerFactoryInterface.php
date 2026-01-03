<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle;

use Psr\Log\LoggerInterface;

interface CslLoggerFactoryInterface
{
    public function createLogger(): LoggerInterface;
}
