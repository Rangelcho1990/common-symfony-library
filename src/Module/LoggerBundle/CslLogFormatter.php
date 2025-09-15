<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle;

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

        return json_encode((object) [
            'timestamp'       => $record['datetime'],
            'level'           => $record['level_name'],
            'messageTemplate' => $record['context']['messageTemplate'] ?? '',
            'additional_data' => (object) [
                'requestUid'             => $record['context']['requestUid'] ?? '',
                'requestBodyStringified' => isset($record['context']['request'])
                    ? (is_array($record['context']['request'])
                        ? $this->replaceDoubleQuoteAndStripSlashes(json_encode($record['context']['request']))
                        : $this->replaceDoubleQuoteAndStripSlashes($record['context']['request']))
                    : '',
                'resource' => $record['context']['resource'] ?? '',
                'method'   => $record['context']['method']   ?? '',
                'ip'       => $record['context']['ip']       ?? '',
                'other'    => isset($record['context']['other'])
                    ? $this->replaceDoubleQuoteAndStripSlashes(
                        json_encode($record['context']['other'], JSON_FORCE_OBJECT)
                    )
                    : '',
                'responseBodyStringified' => isset($record['context']['response'])
                    ? (is_array($record['context']['response'])
                        ? json_encode($record['context']['response'])
                        : $record['context']['response'])
                    : '',
                'message'    => $record['context']['message']    ?? '',
                'file'       => $record['context']['file']       ?? '',
                'line'       => $record['context']['line']       ?? '',
                'stackTrace' => $record['context']['stackTrace'] ?? '',
                'code'       => $record['context']['code']       ?? '',
            ],
        ]).PHP_EOL;
    }

    private function replaceDoubleQuoteAndStripSlashes($str): string
    {
        return stripslashes(str_replace('"', "'", $str));
    }
}
