<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\DTO;

class CslLogTraceDataDTO implements CslLogTraceDataDTOInterface
{
    /**
     * @var array{
     *      messageTemplate: string,
     *      other: string|null,
     *      responseBody: string|null,
     *      message: string|null,
     *      file: string|null,
     *      line: string|null,
     *      stackTrace: array<mixed>|null,
     *      code: int|null
     * }
     */
    private array $data;

    /**
     * @param array<mixed> $stackTrace
     */
    public function prepareLogTraceData(
        string $messageTemplate,
        ?string $other = null,
        ?string $responseBody = null,
        ?string $message = null,
        ?string $file = null,
        ?string $line = null,
        ?array $stackTrace = null,
        ?int $code = null,
    ): void {
        $this->data = [
            'datetime' => (new \DateTimeImmutable())->format(DATE_RFC3339),
            'messageTemplate' => $messageTemplate,
            'other' => $other,
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
     *      other: string|null,
     *      responseBody: string|null,
     *      message: string|null,
     *      file: string|null,
     *      line: string|null,
     *      stackTrace: array<mixed>|null,
     *      code: int|null
     * }
     */
    public function getLogTraceData(): array
    {
        return $this->data;
    }
}
