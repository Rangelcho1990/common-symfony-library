<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Handler;

use CSL\Exceptions\ParameterNotFoundException;
use CSL\Module\LoggerBundle\LoggerFormatters\CslLogFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(id: 'CslStreamHandler', public: true)]
final class CslStreamHandler extends CslAbstractHandlerBuilder
{
    /**
     * @throws ParameterNotFoundException
     */
    public function getHandler(): HandlerInterface
    {
        try {
            $handlerInstance = new StreamHandler(
                $this->getLoggerConfiguration()->getHost(),
                $this->getLogLevel()
            );

            $handlerInstance->setFormatter(
                new CslLogFormatter($this->getLoggerConfiguration()->getFormat())
            );

            return $handlerInstance;
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to create StreamHandler: '.$e->getMessage(), 0, $e);
        }
    }
}
