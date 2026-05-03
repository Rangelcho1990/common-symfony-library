<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Handler;

use CSL\Module\LoggerBundle\DTO\LoggerConfigurationDTO;
use Monolog\Handler\HandlerInterface;

interface CslHandlerInterface
{
    public function setLoggerConfiguration(LoggerConfigurationDTO $loggerConfiguration): void;

    public function getHandler(): HandlerInterface;
}
