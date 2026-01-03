<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Handler\Factory;

use CSL\DTO\Logger\LoggerConfigurationDTO;
use CSL\Exceptions\NotImplementedException;
use CSL\Module\LoggerBundle\Handler\Registry\HandlerRegistryInterface;
use Monolog\Handler\HandlerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class HandlerFactory implements HandlerFactoryInterface
{
    public function __construct(
        private readonly HandlerRegistryInterface $handlerRegistry,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NotImplementedException
     */
    public function createHandler(LoggerConfigurationDTO $config): HandlerInterface
    {
        $handler = $this->handlerRegistry->getHandler(
            $config->getHandlerClass()
        );
        $handler->setLoggerConfiguration($config);

        return $handler->getHandler();
    }
}
