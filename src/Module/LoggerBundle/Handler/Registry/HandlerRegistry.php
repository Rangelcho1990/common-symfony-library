<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Handler\Registry;

use CSL\Exceptions\NotImplementedException;
use CSL\Module\LoggerBundle\Handler\CslHandlerBuilderInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class HandlerRegistry implements HandlerRegistryInterface
{
    /** @var array<string, CslHandlerBuilderInterface> */
    private array $handlers = [];

    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NotImplementedException
     */
    public function getHandler(string $handlerName): CslHandlerBuilderInterface
    {
        if (!isset($this->handlers[$handlerName])) {
            // Try to get from container if not in registry
            if ($this->container->has($handlerName)) {
                $handler = $this->container->get($handlerName);

                if ($handler instanceof CslHandlerBuilderInterface) {
                    $this->registerHandler($handlerName, $handler);

                    return $handler;
                }

                throw new \TypeError('Handler "'.$handlerName.'" must implement CslHandlerBuilderInterface');
            }

            throw new NotImplementedException("Handler '{$handlerName}' not found");
        }

        return $this->handlers[$handlerName];
    }

    public function registerHandler(string $handlerName, CslHandlerBuilderInterface $handler): void
    {
        $this->handlers[$handlerName] = $handler;
    }
}
