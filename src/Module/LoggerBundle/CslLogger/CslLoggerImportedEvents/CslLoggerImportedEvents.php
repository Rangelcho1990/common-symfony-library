<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\CslLogger\CslLoggerImportedEvents;

use CSL\Module\LoggerBundle\DTO\CslLogRequestDataDTOInterface;
use CSL\Module\LoggerBundle\DTO\CslLogTraceDataDTOInterface;
use Psr\Log\LoggerInterface;

class CslLoggerImportedEvents implements CslLoggerImportedEventsInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function logEmergency(
        CslLogRequestDataDTOInterface $cslLogRequestDataDTO,
        CslLogTraceDataDTOInterface $cslLogTraceDataDTO,
    ): void {
        $this->logger->error(
            'Emergency',
            array_merge(
                $cslLogRequestDataDTO->getLogRequestData(),
                $cslLogTraceDataDTO->getLogTraceData()
            )
        );
    }

    public function logAlert(
        CslLogRequestDataDTOInterface $cslLogRequestDataDTO,
        CslLogTraceDataDTOInterface $cslLogTraceDataDTO,
    ): void {
        $this->logger->error(
            'Alert',
            array_merge(
                $cslLogRequestDataDTO->getLogRequestData(),
                $cslLogTraceDataDTO->getLogTraceData()
            )
        );
    }
}
