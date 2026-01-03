<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Handler;

use CSL\DTO\Logger\LoggerConfigurationDTO;
use Monolog\Level;

abstract class CslAbstractHandlerBuilder implements CslHandlerInterface
{
    protected LoggerConfigurationDTO $loggerConfiguration;

    public function setLoggerConfiguration(LoggerConfigurationDTO $loggerConfiguration): void
    {
        $this->loggerConfiguration = $loggerConfiguration;
    }

    protected function getLogLevel(): Level
    {
        if (!isset($this->loggerConfiguration)) {
            throw new \RuntimeException('LoggerConfiguration must be set before getting log level');
        }

        return match ($this->loggerConfiguration->getLevel()) {
            100 => Level::Debug,
            200 => Level::Info,
            250 => Level::Notice,
            300 => Level::Warning,
            400 => Level::Error,
            500 => Level::Critical,
            550 => Level::Alert,
            600 => Level::Emergency,
            default => Level::Error,
        };
    }
}
