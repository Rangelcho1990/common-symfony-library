<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle;

use CSL\DTO\Logger\CslLoggerFactoryDTO;
use CSL\Exceptions\NotImplementedException;
use Monolog\ErrorHandler;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

class CslLoggerFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NotImplementedException
     */
    public function createLogger(CslLoggerFactoryDTO $cslLoggerFactoryDTO): LoggerInterface
    {
        try {
            $loggerConfiguration = $cslLoggerFactoryDTO->getParameterBag()->get('logger');
        } catch (\InvalidArgumentException $exception) {
            throw new \InvalidArgumentException($exception->getMessage());
        }

        if (empty($loggerConfiguration['format'])) {
            throw new \InvalidArgumentException('Missing logger "format"!');
        }

        if (empty($loggerConfiguration['handlers'])) {
            throw new \InvalidArgumentException('Missing logger "handlers"!');
        }

        $handlersInstance = [];
        $container        = $cslLoggerFactoryDTO->getContainer();

        foreach ($loggerConfiguration['handlers'] as $handler => $handlerParams) {
            $handlerClass = 'Csl'.$handler;

            $handlerController = 'CSL\\Module\\LoggerBundle\\Loggers\\'.$handlerClass;
            if (!class_exists($handlerController)) {
                throw new \InvalidArgumentException('Unknown handler "'.$handlerClass.'"');
            }
            unset($handlerController);

            try {
                $handlerParams['name']   = $handler;
                $handlerParams['format'] = $loggerConfiguration['format'];

                $handlersInstance[] = $container->get($handlerClass)->getHandler($handlerParams);

                unset($handlerParams, $handlerClass);
            } catch (\Exception $exception) {
                throw new NotImplementedException($exception->getMessage());
            }
        }

        $loggerInterface = $cslLoggerFactoryDTO->getLoggerInterface();
        $loggerInterface->setHandlers($handlersInstance);
        unset($loggerConfiguration, $handlersInstance);

        ErrorHandler::register($loggerInterface);

        return $loggerInterface;
    }
}
