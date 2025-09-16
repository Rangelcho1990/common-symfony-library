<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Loggers;

use CSL\Module\LoggerBundle\CslLogFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(id: 'CslStreamHandler', public: true)]
class CslStreamHandler implements CslHandlerInterface
{
    /**
     * @param array<string, string> $handlerParams
     */
    public function getHandler(array $handlerParams): HandlerInterface
    {
        $handler   = '\\Monolog\\Handler\\'.$handlerParams['name'];
        $formatter = new CslLogFormatter($handlerParams['format']);
        unset($handlerParams['name'], $handlerParams['format']);

        $params = array_values($handlerParams);

        /** @var StreamHandler $handlerInstance */
        $handlerInstance = new $handler(...$params);
        $handlerInstance->setFormatter($formatter);

        unset($handler, $formatter, $params);

        /* @var HandlerInterface */
        return $handlerInstance;
    }
}
