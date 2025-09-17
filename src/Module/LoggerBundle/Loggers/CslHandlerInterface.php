<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Loggers;

use CSL\DTO\Logger\LoggerConfigurationDTO;
use Monolog\Handler\HandlerInterface;

interface CslHandlerInterface
{
    public function setLoggerConfiguration(LoggerConfigurationDTO $loggerConfiguration): void;

    public function getHandler(): HandlerInterface;
}
