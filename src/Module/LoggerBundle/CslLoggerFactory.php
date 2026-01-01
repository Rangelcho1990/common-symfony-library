<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle;

use CSL\DTO\Logger\CslLoggerFactoryDTO;
use CSL\DTO\Logger\LoggerConfigurationDTO;
use CSL\Exceptions\NotImplementedException;
use CSL\Module\ErrorHandler\AbstractErrorHandler;
use CSL\Module\LoggerBundle\Handler\Factory\HandlerFactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

class CslLoggerFactory
{
    public function __construct(
        private readonly CslLoggerFactoryDTO $cslLoggerFactoryDTO,
        private readonly AbstractErrorHandler $abstractErrorHandler,
        private readonly HandlerFactoryInterface $handlerFactory,
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

        $handlersInstance = [];

        foreach ($handlers as $handler => $handlerParams) {
            $loggerConfiguration = new LoggerConfigurationDTO();
            $loggerConfiguration->prepareConfigurationData($handler, $handlerParams);

            if (!class_exists($loggerConfiguration->getHandlerNamespace())) {
                throw new NotImplementedException('Unknown handler "'.$loggerConfiguration->getHandlerClass().'"');
            }

            $handlersInstance[] = $this->handlerFactory->createHandler($loggerConfiguration);
        }

        $logger = $this->cslLoggerFactoryDTO->getMonologLogger();
        $logger->setHandlers($handlersInstance);

        $this->abstractErrorHandler->handle($logger);

        return $logger;
    }
}
