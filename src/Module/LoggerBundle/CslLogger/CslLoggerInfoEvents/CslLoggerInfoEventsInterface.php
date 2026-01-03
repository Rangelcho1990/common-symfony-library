<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\CslLogger\CslLoggerInfoEvents;

use CSL\Module\LoggerBundle\DTO\CslLogRequestDataDTOInterface;
use CSL\Module\LoggerBundle\DTO\CslLogTraceDataDTOInterface;

interface CslLoggerInfoEventsInterface
{
    public function logDebug(
        CslLogRequestDataDTOInterface $cslLogRequestDataDTO,
        CslLogTraceDataDTOInterface $cslLogTraceDataDTO,
    ): void;

    public function logInfo(
        CslLogRequestDataDTOInterface $cslLogRequestDataDTO,
        CslLogTraceDataDTOInterface $cslLogTraceDataDTO,
    ): void;

    public function logNotice(
        CslLogRequestDataDTOInterface $cslLogRequestDataDTO,
        CslLogTraceDataDTOInterface $cslLogTraceDataDTO,
    ): void;
}
