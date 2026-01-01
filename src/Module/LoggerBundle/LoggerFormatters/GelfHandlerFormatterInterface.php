<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\LoggerFormatters;

use Gelf\Message;
use Monolog\LogRecord;

interface GelfHandlerFormatterInterface
{
    public function format(LogRecord $record): Message;
}
