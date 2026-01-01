<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\LoggerFormatters;

use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

class CslLogFormatter extends LineFormatter implements CslLogFormatterInterface
{
    public function format(LogRecord $record): string
    {
        return json_encode([
            'timestamp' => $record->context['datetime'] ?? $record->datetime->format('c'),
            'level' => $record->level->getName(),
            'messageTemplate' => $record->context['messageTemplate'] ?? null,
            'requestUid' => $record->context['requestUid'] ?? null,
            'requestBody' => $record->context['requestBody'] ?? null,
            'resource' => $record->context['resource'] ?? null,
            'method' => $record->context['method'] ?? null,
            'ip' => $record->context['ip'] ?? null,
            'other' => $record->context['other'] ?? null,
            'responseBody' => $record->context['responseBody'] ?? null,
            'message' => $record->context['message'] ?? null,
            'file' => $record->context['file'] ?? null,
            'line' => $record->context['line'] ?? null,
            'code' => $record->context['code'] ? $record->context['code'] : $record->level->fromName($record->level->getName()),
            'stackTrace' => $record->context['stackTrace'] ?? null,
        ],
            JSON_THROW_ON_ERROR
        ).PHP_EOL;
    }
}
