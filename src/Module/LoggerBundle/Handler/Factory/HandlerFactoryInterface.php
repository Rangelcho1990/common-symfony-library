<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Handler\Factory;

use CSL\DTO\Logger\LoggerConfigurationDTO;
use Monolog\Handler\HandlerInterface;

interface HandlerFactoryInterface
{
    public function createHandler(string $handlerClass, LoggerConfigurationDTO $config): HandlerInterface;
}
