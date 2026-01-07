<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\CslLogger\CslLoggerInfoEvents;

use CSL\Module\LoggerBundle\DTO\CslLogRequestDataDTOInterface;
use CSL\Module\LoggerBundle\DTO\CslLogTraceDataDTOInterface;
use Psr\Log\LoggerInterface;

class CslLoggerInfoEvents implements CslLoggerInfoEventsInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function logDebug(
        CslLogRequestDataDTOInterface $cslLogRequestDataDTO,
        CslLogTraceDataDTOInterface $cslLogTraceDataDTO,
    ): void {
        $this->logger->debug(
            'Debug',
            array_merge(
                $cslLogRequestDataDTO->getLogRequestData(),
                $cslLogTraceDataDTO->getLogTraceData()
            )
        );
    }

    public function logInfo(
        CslLogRequestDataDTOInterface $cslLogRequestDataDTO,
        CslLogTraceDataDTOInterface $cslLogTraceDataDTO,
    ): void {
        $this->logger->info(
            'Info',
            array_merge(
                $cslLogRequestDataDTO->getLogRequestData(),
                $cslLogTraceDataDTO->getLogTraceData()
            )
        );
    }

    public function logNotice(
        CslLogRequestDataDTOInterface $cslLogRequestDataDTO,
        CslLogTraceDataDTOInterface $cslLogTraceDataDTO,
    ): void {
        $this->logger->notice(
            'Notice',
            array_merge(
                $cslLogRequestDataDTO->getLogRequestData(),
                $cslLogTraceDataDTO->getLogTraceData()
            )
        );
    }
}
