<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Handler\Factory;

use CSL\Module\LoggerBundle\DTO\LoggerConfigurationDTO;
use Monolog\Handler\HandlerInterface;

interface HandlerFactoryInterface
{
    public function createHandler(LoggerConfigurationDTO $config): HandlerInterface;
}
