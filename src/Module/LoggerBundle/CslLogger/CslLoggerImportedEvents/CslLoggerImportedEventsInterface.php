<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\CslLogger\CslLoggerImportedEvents;

use CSL\Module\LoggerBundle\DTO\CslLogRequestDataDTOInterface;
use CSL\Module\LoggerBundle\DTO\CslLogTraceDataDTOInterface;

interface CslLoggerImportedEventsInterface
{
    public function logEmergency(
        CslLogRequestDataDTOInterface $cslLogRequestDataDTO,
        CslLogTraceDataDTOInterface $cslLogTraceDataDTO,
    ): void;

    public function logAlert(
        CslLogRequestDataDTOInterface $cslLogRequestDataDTO,
        CslLogTraceDataDTOInterface $cslLogTraceDataDTO,
    ): void;
}
