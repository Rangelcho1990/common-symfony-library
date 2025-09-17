<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Loggers;

use CSL\DTO\Logger\LoggeConfigurationDTO;
use Monolog\Level;

abstract class AbstractHandlerBuilder implements CslHandlerInterface
{
    protected LoggeConfigurationDTO $loggerConfiguration;

    public function setLoggerConfiguration(LoggeConfigurationDTO $loggerConfiguration): void
    {
        $this->loggerConfiguration = $loggerConfiguration;
    }

    protected function getLogLevel(): Level
    {
        return match ($this->loggerConfiguration->getLevel()) {
            100     => Level::Debug,
            200     => Level::Info,
            250     => Level::Notice,
            300     => Level::Warning,
            500     => Level::Critical,
            550     => Level::Alert,
            600     => Level::Emergency,
            default => Level::Error,
        };
    }
}
