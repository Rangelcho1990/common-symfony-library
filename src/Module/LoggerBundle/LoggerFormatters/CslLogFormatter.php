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
        $format = null,
        $dateFormat = null,
        bool $allowInlineLineBreaks = false,
        bool $ignoreEmptyContextAndExtra = false,
    ) {
        parent::__construct($format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra);
    }

    public function format(LogRecord $record): string
    {
        $record = $record->toArray();

        $responseBody = $other = $requestBody = '';
        if (isset($record['context']['request']) && is_string($record['context']['request'])) {
            $requestBody = json_encode(
                $record['context']['request'],
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
        }

        if (isset($record['context']['other']) && is_string($record['context']['other'])) {
            $other = json_encode(
                $record['context']['other'],
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
        }

        if (isset($record['context']['response']) && is_string($record['context']['response'])) {
            $responseBody = json_encode(
                $record['context']['response'],
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
        }

        return json_encode((object) [
            'timestamp' => $record['datetime']->format(DATE_RFC3339),
            'level' => $record['level_name'],
            'messageTemplate' => $record['context']['messageTemplate'] ?? '',
            'additional_data' => (object) [
                'requestUid' => $record['context']['requestUid'] ?? '',
                'requestBody' => $requestBody,
                'resource' => $record['context']['resource'] ?? '',
                'method' => $record['context']['method'] ?? '',
                'ip' => $record['context']['ip'] ?? '',
                'other' => $other,
                'responseBody' => $responseBody,
                'message' => $record['context']['message'] ?? '',
                'file' => $record['context']['file'] ?? '',
                'line' => $record['context']['line'] ?? '',
                'stackTrace' => $record['context']['stackTrace'] ?? '',
                'code' => $record['context']['code'] ?? '',
            ],
        ]).PHP_EOL;
    }
}
