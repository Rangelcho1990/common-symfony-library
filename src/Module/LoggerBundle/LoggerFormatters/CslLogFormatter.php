<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\LoggerFormatters;

use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

class CslLogFormatter extends LineFormatter
{
    /**
     * @param string $format                The format of the message
     * @param string $dateFormat            The format of the timestamp: one supported by DateTime::format
     * @param bool   $allowInlineLineBreaks Whether to allow inline line breaks in log entries
     */
    public function __construct(
        ?string $format = null,
        ?string $dateFormat = null,
        bool $allowInlineLineBreaks = false,
        bool $ignoreEmptyContextAndExtra = false,
    ) {
        parent::__construct($format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);
    }

    public function format(LogRecord $record): string
    {
        return json_encode([
            'timestamp' => $record->context['datetime'],
            'level' => $record['level_name'],
            'messageTemplate' => $record->context['messageTemplate'],
            'requestUid' => $record->context['requestUid'],
            'requestBody' => $record->context['requestBody'],
            'resource' => $record->context['resource'],
            'method' => $record->context['method'],
            'ip' => $record->context['ip'],
            'other' => $record->context['other'],
            'responseBody' => $record->context['responseBody'],
            'message' => $record->context['message'],
            'file' => $record->context['file'],
            'line' => $record->context['line'],
            'code' => $record->context['code'],
            'stackTrace' => $record->context['stackTrace'],
        ]).PHP_EOL;
    }
}
