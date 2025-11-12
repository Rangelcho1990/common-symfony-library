<?php

declare(strict_types=1);

namespace CSL\DTO\Logger;

use Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Psr\Log\LoggerInterface;

class CslLoggerFactoryDTO
{
    public function __construct(
        private readonly LoggerInterface $monologLogger,
        private readonly ContainerBagInterface $parameterBag,
        private readonly ContainerInterface $container,
    ) {
    }

    public function getMonologLogger(): Logger
    {
        return $this->monologLogger;
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
