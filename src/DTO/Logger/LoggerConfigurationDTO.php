<?php

declare(strict_types=1);

namespace CSL\DTO\Logger;

class LoggerConfigurationDTO
{
    private const PREFIX_NAME = 'Csl';
    private const PREFIX_NAMESPACE = 'CSL\\Module\\LoggerBundle\\Handler\\';

    private string $handlerClass;
    private string $handlerNamespace;
    private int $level;
    private string $format;
    private string $host;
    private ?int $port = null;
    private ?string $source = null;
    private ?bool $ignoreConnectionErrors = null;

    /**
     * @param array{
     *    level: int,
     *    format: string,
     *    host: string,
     *    port: int|null,
     *    source: string|null,
     *    ignoreConnectionErrors: bool|null
     * } $handlerParams
     */
    public function prepareConfigurationData(string $handler, array $handlerParams): void
    {
        $this->handlerClass = self::PREFIX_NAME.$handler;
        $this->handlerNamespace = self::PREFIX_NAMESPACE.self::PREFIX_NAME.$handler;
        $this->host = $handlerParams['host'];
        $this->level = $handlerParams['level'];
        $this->format = $handlerParams['format'];

        // Use array_key_exists to check if key exists, allowing false values
        if (array_key_exists('port', $handlerParams) && !empty($handlerParams['port'])) {
            $this->port = $handlerParams['port'];
        }

        if (array_key_exists('source', $handlerParams) && !empty($handlerParams['source'])) {
            $this->source = $handlerParams['source'];
        }

        // For boolean, check if key exists (allows false values)
        if (array_key_exists('ignoreConnectionErrors', $handlerParams)) {
            $this->ignoreConnectionErrors = (bool) $handlerParams['ignoreConnectionErrors'];
        }
    }

    public function getHandlerClass(): string
    {
        return $this->handlerClass;
    }

    public function getHandlerNamespace(): string
    {
        return $this->handlerNamespace;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function getIgnoreConnectionErrors(): ?bool
    {
        return $this->ignoreConnectionErrors;
    }
}
