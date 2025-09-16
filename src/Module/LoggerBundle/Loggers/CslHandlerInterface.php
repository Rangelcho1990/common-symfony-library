<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Loggers;

use Monolog\Handler\HandlerInterface;

interface CslHandlerInterface
{
    /**
     * @param array<string, string> $handlerParams
     */
    public function getHandler(array $handlerParams): HandlerInterface;
}
