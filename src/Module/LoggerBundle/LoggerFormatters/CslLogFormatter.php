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
            $requestBody = $this->replaceDoubleQuoteAndStripSlashes($record['context']['request']);
        }

        if (isset($record['context']['other']) && is_string($record['context']['other'])) {
            $other = $this->replaceDoubleQuoteAndStripSlashes($record['context']['other']);
        }

        if (isset($record['context']['response']) && is_string($record['context']['response'])) {
            $responseBody = $this->replaceDoubleQuoteAndStripSlashes($record['context']['response']);
        }

        return json_encode((object) [
            'timestamp'       => $record['datetime'],
            'level'           => $record['level_name'],
            'messageTemplate' => $record['context']['messageTemplate'] ?? '',
            'additional_data' => (object) [
                'requestUid'             => $record['context']['requestUid'] ?? '',
                'requestBody'            => $requestBody,
                'resource'               => $record['context']['resource'] ?? '',
                'method'                 => $record['context']['method']   ?? '',
                'ip'                     => $record['context']['ip']       ?? '',
                'other'                  => $other,
                'responseBody'           => $responseBody,
                'message'                => $record['context']['message']    ?? '',
                'file'                   => $record['context']['file']       ?? '',
                'line'                   => $record['context']['line']       ?? '',
                'stackTrace'             => $record['context']['stackTrace'] ?? '',
                'code'                   => $record['context']['code']       ?? '',
            ],
        ]).PHP_EOL;
    }

    private function replaceDoubleQuoteAndStripSlashes(string $jsonData): string
    {
        return stripslashes(str_replace('"', "'", $jsonData));
    }
}
