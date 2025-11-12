<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle;

use CSL\DTO\Logger\CslLoggerFactoryDTO;
use CSL\DTO\Logger\LoggerConfigurationDTO;
use CSL\Exceptions\NotImplementedException;
use CSL\Module\ErrorHandler\AbstractErrorHandler;
use CSL\Module\LoggerBundle\Loggers\CslHandlerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

class CslLoggerFactory
{
    public function __construct(
        private readonly CslLoggerFactoryDTO $cslLoggerFactoryDTO,
        private readonly AbstractErrorHandler $abstractErrorHandler,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NotImplementedException
     */
    public function createLogger(): LoggerInterface
    {
        /**
         * @var array{
         *    string: array{
         *      level: int,
         *      format: string,
         *      host: string,
         *      port: int|null,
         *      source: string|null,
         *      ignoreConnectionErrors: bool|null
         *    }
         * } $handlers
         */
        $handlers = $this->cslLoggerFactoryDTO->getParameterBag()->get('handlers');
        if (!is_array($handlers)) {
            throw new \InvalidArgumentException('Handlers configuration must be an array');
        }

        if (empty($handlers)) {
            throw new \InvalidArgumentException('At least one handler must be configured');
        }

        $handlersInstance = [];
        $container = $this->cslLoggerFactoryDTO->getContainer();

        foreach ($handlers as $handler => $handlerParams) {
            if (!is_array($handlerParams)) {
                throw new \InvalidArgumentException("Handler configuration for '{$handler}' must be an array");
            }

            $loggerConfiguration = new LoggerConfigurationDTO();
            $loggerConfiguration->prepareConfigurationData($handler, $handlerParams);

            if (!class_exists($loggerConfiguration->getHandlerNamespace())) {
                throw new NotImplementedException('Unknown handler "'.$loggerConfiguration->getHandlerClass().'"');
            }

            /** @var CslHandlerInterface $handlerClass */
            $handlerClass = $container->get($loggerConfiguration->getHandlerClass());
            if (!$handlerClass instanceof CslHandlerInterface) {
                throw new \TypeError('Handler "'.$loggerConfiguration->getHandlerClass().'" must implement CslHandlerInterface');
            }

            $handlerClass->setLoggerConfiguration($loggerConfiguration);
            $handlersInstance[] = $handlerClass->getHandler();
        }

        $logger = $this->cslLoggerFactoryDTO->getMonologLogger();
        $logger->setHandlers($handlersInstance);

        $this->abstractErrorHandler->handle($logger);

        return $logger;
    }
}
