<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Handler\Registry;

use CSL\Module\LoggerBundle\Handler\CslHandlerInterface;

interface HandlerRegistryInterface
{
    public function getHandler(string $handlerName): CslHandlerInterface;

    public function hasHandler(string $handlerName): bool;

    public function registerHandler(string $handlerName, CslHandlerInterface $handler): void;
}
