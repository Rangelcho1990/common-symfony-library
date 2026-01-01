<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\CslLogger;

use CSL\Module\LoggerBundle\DTO\CslLogRequestDataDTOInterface;
use CSL\Module\LoggerBundle\DTO\CslLogTraceDataDTOInterface;
use Psr\Log\LoggerInterface;

final class CslLogger implements CslLoggerInterface, CslLoggerEventsInterface
{
    /** @var array<string, mixed> */
    private array $context = [];

    public function __construct(private readonly LoggerInterface $logger)
    {
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

    /**
     * @param array<string, mixed> $context
     */
    public function addContext(array $context): void
    {
        if (!empty($context)) {
            $this->context = array_merge($this->context, $context);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
