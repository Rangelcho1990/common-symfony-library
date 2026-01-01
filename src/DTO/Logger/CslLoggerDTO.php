<?php

declare(strict_types=1);

namespace CSL\DTO\Logger;

use CSL\Exceptions\NotImplementedException;
use CSL\Module\LoggerBundle\CslLogger\CslLogger;
use CSL\Module\LoggerBundle\CslLoggerFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CslLoggerDTO
{
    private CslLogger $cslLogger;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NotImplementedException
     */
    public function __construct(CslLoggerFactory $loggerFactory)
    {
        $this->cslLogger = new CslLogger(
            $loggerFactory->createLogger()
        );
    }

    public function getCslLogger(): CslLogger
    {
        return $this->cslLogger;
    }
}
