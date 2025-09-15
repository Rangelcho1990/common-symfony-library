<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Loggers;

use Monolog\Handler\HandlerInterface;

interface CslHandlerInterface
{
    public function getHandler(array $handlerParams): HandlerInterface;
}
