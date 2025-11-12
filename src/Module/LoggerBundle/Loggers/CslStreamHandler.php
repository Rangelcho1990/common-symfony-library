<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Loggers;

use CSL\Module\LoggerBundle\LoggerFormatters\CslLogFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(id: 'CslStreamHandler', public: true)]
final class CslStreamHandler extends CslAbstractHandlerBuilder
{
    public function getHandler(): HandlerInterface
    {
        try {
            $handlerInstance = new StreamHandler(
                $this->loggerConfiguration->getHost(),
                $this->getLogLevel()
            );

            $handlerInstance->setFormatter(
                new CslLogFormatter($this->loggerConfiguration->getFormat())
            );

            return $handlerInstance;
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to create StreamHandler: '.$e->getMessage(), 0, $e);
        }
    }
}
