<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\CslLogger;

use CSL\Module\LoggerBundle\DTO\CslLogRequestDataDTOInterface;
use CSL\Module\LoggerBundle\DTO\CslLogTraceDataDTOInterface;

interface CslLoggerEventsInterface
{
    public function logError(
        CslLogRequestDataDTOInterface $cslLogRequestDataDTO,
        CslLogTraceDataDTOInterface $cslLogTraceDataDTO,
    ): void;
}
