<?php

declare(strict_types=1);

namespace CSL\DTO\Logger;

class LoggerConfigurationDTO
{
    private const PREFIX_NAME       = 'Csl';
    private const PREFIX_CONTROLLER = 'CSL\\Module\\LoggerBundle\\Loggers\\';

    private string $handlerClass;
    private string $handlerNamespace;
    private int $level;
    private string $format;
    private string $host;
    private ?int $port                    = null;
    private ?string $source               = null;
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
        $this->setHandlerClass($handler);
        $this->setHandlerNamespace($handler);
        $this->setHost($handlerParams['host']);
        $this->setLevel($handlerParams['level']);
        $this->setFormat($handlerParams['format']);

        if (!empty($handlerParams['port'])) {
            $this->setPort($handlerParams['port']);
        }

        if (!empty($handlerParams['source'])) {
            $this->setSource($handlerParams['source']);
        }

        if (!empty($handlerParams['ignoreConnectionErrors'])) {
            $this->setIgnoreConnectionErrors($handlerParams['ignoreConnectionErrors']);
        }
    }

    public function setHandlerClass(string $handlerName): self
    {
        $this->handlerClass = self::PREFIX_NAME.$handlerName;

        return $this;
    }

    public function getHandlerClass(): string
    {
        return $this->handlerClass;
    }

    public function setHandlerNamespace(string $handlerName): self
    {
        $this->handlerNamespace = self::PREFIX_CONTROLLER.self::PREFIX_NAME.$handlerName;

        return $this;
    }

    public function getHandlerNamespace(): string
    {
        return $this->handlerNamespace;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function setPort(?int $port): self
    {
        $this->port = $port;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getIgnoreConnectionErrors(): ?bool
    {
        return $this->ignoreConnectionErrors;
    }

    public function setIgnoreConnectionErrors(?bool $ignoreConnectionErrors): self
    {
        $this->ignoreConnectionErrors = $ignoreConnectionErrors;

        return $this;
    }
}
