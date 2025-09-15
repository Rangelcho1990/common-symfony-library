<?php

declare(strict_types=1);

namespace CSL\DTO\Logger;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class CslLoggerFactoryDTO
{
    private LoggerInterface $loggerInterface;
    private ContainerBagInterface $parameterBag;
    private ContainerInterface $container;

    public function __construct(
        LoggerInterface $loggerInterface,
        ContainerBagInterface $parameterBag,
        ContainerInterface $container,
    ) {
        $this->loggerInterface = $loggerInterface;
        $this->parameterBag    = $parameterBag;
        $this->container       = $container;
    }

    public function getLoggerInterface(): LoggerInterface
    {
        return $this->loggerInterface;
    }

    public function getParameterBag(): ContainerBagInterface
    {
        return $this->parameterBag;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
