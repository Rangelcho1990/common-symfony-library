<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Handler;

use CSL\Module\LoggerBundle\DTO\LoggerConfigurationDTO;
use Monolog\Handler\HandlerInterface;

interface CslHandlerBuilderInterface
{
    public function setLoggerConfiguration(LoggerConfigurationDTO $loggerConfiguration): void;

    public function getLoggerConfiguration(): LoggerConfigurationDTO;

    public function getHandler(): HandlerInterface;
}
