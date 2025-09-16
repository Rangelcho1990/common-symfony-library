<?php

declare(strict_types=1);

namespace CSL\DTO\Logger;

use Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class CslLoggerFactoryDTO
{
    private Logger $logger;
    private ContainerBagInterface $parameterBag;
    private ContainerInterface $container;

    public function __construct(
        Logger $monologLogger,
        ContainerBagInterface $parameterBag,
        ContainerInterface $container,
    ) {
        $this->logger       = $monologLogger;
        $this->parameterBag = $parameterBag;
        $this->container    = $container;
    }

    public function getMonologLogger(): Logger
    {
        return $this->logger;
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
