<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Loggers;

use CSL\DTO\Logger\LoggeConfigurationDTO;
use Monolog\Handler\HandlerInterface;

interface CslHandlerInterface
{
    public function setLoggerConfiguration(LoggeConfigurationDTO $loggerConfiguration): void;

    public function getHandler(): HandlerInterface;
}
