<?php

declare(strict_types=1);

namespace CSL\DTO\Logger;

use Monolog\Logger;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class CslLoggerFactoryDTO
{
    public function __construct(
        private readonly Logger $monologLogger,
        private readonly ContainerBagInterface $parameterBag,
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
}
