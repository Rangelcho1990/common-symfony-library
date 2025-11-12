<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\DTO;

use Ramsey\Uuid\UuidInterface;

class CslLogDataDTO
{
    private string $messageTemplate;
    private ?UuidInterface $requestUid = null;
    private string $requestBody = '';
    private string $resource = '';
    private string $method = '';
    private string $ip = '';
    private string $other = '';
    private string $responseBody = '';
    private string $message = '';
    private string $file = '';
    private string $line = '';

    /** @var array<mixed> */
    private array $stackTrace = [];
    private int $code = 0;

    /**
     * @return array{
     *   messageTemplate: string,
     *      requestUid: UuidInterface|null,
     *      requestBody: string,
     *      resource: string,
     *      method: string,
     *      ip: string,
     *      other: string,
     *      responseBody: string,
     *      message: string,
     *      file: string,
     *      line: string,
     *      stackTrace: array<mixed>,
     *      code: int
     * }
     */
    public function getLogData(): array
    {
        return [
            'datetime' => (new \DateTimeImmutable())->format(DATE_RFC3339),
            'messageTemplate' => $this->messageTemplate,
            'requestUid' => $this->requestUid,
            'requestBody' => $this->requestBody,
            'resource' => $this->resource,
            'method' => $this->method,
            'ip' => $this->ip,
            'other' => $this->other,
            'responseBody' => $this->responseBody,
            'message' => $this->message,
            'file' => $this->file,
            'line' => $this->line,
            'stackTrace' => $this->stackTrace,
            'code' => $this->code,
        ];
    }

    public function setMessageTemplate(string $messageTemplate): void
    {
        $this->messageTemplate = $messageTemplate;
    }

    public function setRequestUid(?UuidInterface $requestUid): void
    {
        $this->requestUid = $requestUid;
    }

    public function setRequestBody(string $requestBody): void
    {
        $this->requestBody = $requestBody;
    }

    public function setResource(string $resource): void
    {
        $this->resource = $resource;
    }

    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    public function setOther(string $other): void
    {
        $this->other = $other;
    }

    public function setResponseBody(string $responseBody): void
    {
        $this->responseBody = $responseBody;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    public function setLine(string $line): void
    {
        $this->line = $line;
    }

    /** @param array<mixed> $stackTrace */
    public function setStackTrace(array $stackTrace): void
    {
        $this->stackTrace = $stackTrace;
    }

    public function setCode(int $code): void
    {
        $this->code = $code;
    }
}
