<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\DTO;

class CslLogTraceDataDTO implements CslLogTraceDataDTOInterface
{
    /**
     * @var array{
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
    private array $data;

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
    ): void {
        $this->data = [
            'datetime' => (new \DateTimeImmutable())->format(DATE_RFC3339),
            'messageTemplate' => $messageTemplate,
            'communicationTime' => $communicationTime,
            'responseBody' => $responseBody,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'stackTrace' => $stackTrace,
            'code' => $code,
        ];
    }

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
    public function getLogTraceData(): array
    {
        return $this->data;
    }
}
