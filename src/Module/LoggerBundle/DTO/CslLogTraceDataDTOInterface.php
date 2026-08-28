<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\DTO;

interface CslLogTraceDataDTOInterface
{
    /**
     * @param array{startTime?: float, endTime?: float, durationMs?: int} $communicationTime
     * @param array<mixed>                                                $stackTrace
     */
    public function prepareLogTraceData(
        string $messageTemplate,
        ?array $communicationTime = null,
        ?string $responseBody = null,
        ?string $message = null,
        ?string $file = null,
        ?int $line = null,
        ?array $stackTrace = null,
        ?int $code = null,
    ): void;

    /**
     * @return array{
     *      messageTemplate: string,
     *      communicationTime: array{startTime?: float, endTime?: float, durationMs?: int}|null,
     *      responseBody: string|null,
     *      message: string|null,
     *      file: string|null,
     *      line: int|null,
     *      stackTrace: array<mixed>|null,
     *      code: int|null
     * }
     */
    public function getLogTraceData(): array;
}
