<?php

declare(strict_types=1);

namespace CSL\Module\ErrorHandler;

use Monolog\ErrorHandler;
use Monolog\Logger;

class AbstractErrorHandler
{
    public function handle(Logger $logger): void
    {
        ErrorHandler::register($logger);
    }
}
