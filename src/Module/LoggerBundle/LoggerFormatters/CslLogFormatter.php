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
        $context = $record->context;

        $data = [
            'timestamp' => $context['datetime'] ?? $record->datetime->format('c'),
            'level' => $record->level->getName(),
            'messageTemplate' => $context['messageTemplate'] ?? null,
            'requestUid' => $context['requestUid'] ?? null,
            'requestBody' => $context['requestBody'] ?? null,
            'resource' => $context['resource'] ?? null,
            'method' => $context['method'] ?? null,
            'ip' => $context['ip'] ?? null,
            'other' => $context['other'] ?? null,
            'responseBody' => $context['responseBody'] ?? null,
            'message' => $context['message'] ?? $record->message,
            'file' => $context['file'] ?? $record->extra['file'] ?? null,
            'line' => $context['line'] ?? $record->extra['line'] ?? null,
            'code' => $context['code'] ?? null,
            'stackTrace' => $context['stackTrace'] ?? null,
        ];

        $json = json_encode($data, JSON_THROW_ON_ERROR);

        return $json.PHP_EOL;
    }
}
