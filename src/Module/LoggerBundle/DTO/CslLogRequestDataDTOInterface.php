<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\DTO;

use Ramsey\Uuid\UuidInterface;

interface CslLogRequestDataDTOInterface
{
    /**
     * @param array<mixed> $requestBody
     * @param array<mixed> $ip
     */
    public function prepareLogRequestData(
        array $requestBody,
        string $resource,
        string $method,
        ?UuidInterface $requestUid = null,
        ?array $ip = null,
    ): void;

    /**
     * @return array{
     *   requestUid: UuidInterface|null,
     *   requestBody: array<mixed>,
     *   resource: string,
     *   method: string,
     *   ip: array<mixed>|null
     * }
     */
    public function getLogRequestData(): array;
}
