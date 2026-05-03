<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Handler\Registry;

use CSL\Module\LoggerBundle\Handler\CslHandlerBuilderInterface;

interface HandlerRegistryInterface
{
    public function getHandler(string $handlerName): CslHandlerBuilderInterface;

    public function registerHandler(string $handlerName, CslHandlerBuilderInterface $handler): void;
}
