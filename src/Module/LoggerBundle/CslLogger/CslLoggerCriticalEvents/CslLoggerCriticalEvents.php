<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\CslLogger\CslLoggerCriticalEvents;

use CSL\Module\LoggerBundle\DTO\CslLogRequestDataDTOInterface;
use CSL\Module\LoggerBundle\DTO\CslLogTraceDataDTOInterface;
use Psr\Log\LoggerInterface;

class CslLoggerCriticalEvents implements CslLoggerCriticalEventsInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function logCritical(
        CslLogRequestDataDTOInterface $cslLogRequestDataDTO,
        CslLogTraceDataDTOInterface $cslLogTraceDataDTO,
    ): void {
        $this->logger->error(
            'Critical',
            array_merge(
                $cslLogRequestDataDTO->getLogRequestData(),
                $cslLogTraceDataDTO->getLogTraceData()
            )
        );
    }

    public function logError(
        CslLogRequestDataDTOInterface $cslLogRequestDataDTO,
        CslLogTraceDataDTOInterface $cslLogTraceDataDTO,
    ): void {
        $this->logger->error(
            'Error',
            array_merge(
                $cslLogRequestDataDTO->getLogRequestData(),
                $cslLogTraceDataDTO->getLogTraceData()
            )
        );
    }

    public function logWarning(
        CslLogRequestDataDTOInterface $cslLogRequestDataDTO,
        CslLogTraceDataDTOInterface $cslLogTraceDataDTO,
    ): void {
        $this->logger->error(
            'Warning',
            array_merge(
                $cslLogRequestDataDTO->getLogRequestData(),
                $cslLogTraceDataDTO->getLogTraceData()
            )
        );
    }
}
