<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Loggers;

use CSL\Module\LoggerBundle\CslLogFormatter;
use Monolog\Handler\HandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(id: 'CslStreamHandler', public: true)]
class CslStreamHandler implements CslHandlerInterface
{
    public function getHandler(array $handlerParams): HandlerInterface
    {
        $handler   = '\\Monolog\\Handler\\'.$handlerParams['name'];
        $formatter = new CslLogFormatter($handlerParams['format']);
        unset($handlerParams['name'], $handlerParams['format']);

        $params = array_values($handlerParams);

        $handlerInstance = new $handler(...$params);
        $handlerInstance->setFormatter($formatter);

        unset($handler, $formatter, $params);

        return $handlerInstance;
    }
}
