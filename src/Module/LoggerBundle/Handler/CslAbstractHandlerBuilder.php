<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Handler;

use CSL\Module\LoggerBundle\DTO\LoggerConfigurationDTO;
use Monolog\Level;

abstract class CslAbstractHandlerBuilder implements CslHandlerBuilderInterface
{
    private LoggerConfigurationDTO $loggerConfiguration;

    public function setLoggerConfiguration(LoggerConfigurationDTO $loggerConfiguration): void
    {
        $this->loggerConfiguration = $loggerConfiguration;
    }

    public function getLoggerConfiguration(): LoggerConfigurationDTO
    {
        if (!isset($this->loggerConfiguration)) {
            throw new \RuntimeException('LoggerConfiguration must be set before getting log level');
        }

        return $this->loggerConfiguration;
    }

    protected function getLogLevel(): Level
    {
        $configuredLevel = $this->getLoggerConfiguration()->getLevel();
        $level = Level::tryFrom($configuredLevel);
        if (null === $level) {
            throw new \InvalidArgumentException(sprintf('Invalid Monolog log level "%d"', $configuredLevel));
        }

        return $level;
    }
}
