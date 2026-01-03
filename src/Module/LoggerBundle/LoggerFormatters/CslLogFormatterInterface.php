<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\LoggerFormatters;

use Monolog\LogRecord;

interface CslLogFormatterInterface
{
    public function format(LogRecord $record): string;
}
