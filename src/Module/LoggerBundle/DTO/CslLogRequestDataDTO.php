<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\DTO;

use Ramsey\Uuid\UuidInterface;

class CslLogRequestDataDTO implements CslLogRequestDataDTOInterface
{
    /**
     * @var array{
     *   requestUid: UuidInterface|null,
     *   requestBody: array<mixed>,
     *   resource: string,
     *   method: string,
     *   ip: array<mixed>|null
     * }
     */
    private array $requestData;

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
    ): void {
        $this->requestData = [
            'requestBody' => $requestBody,
            'resource' => $resource,
            'method' => $method,
            'requestUid' => $requestUid,
            'ip' => $ip,
        ];
    }

    /**
     * @return array{
     *   requestUid: UuidInterface|null,
     *   requestBody: array<mixed>,
     *   resource: string,
     *   method: string,
     *   ip: array<mixed>|null
     * }
     */
    public function getLogRequestData(): array
    {
        return $this->requestData;
    }
}
