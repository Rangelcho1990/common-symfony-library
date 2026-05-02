<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\LoggerBundle\DTO;

use CSL\Module\LoggerBundle\DTO\CslLogRequestDataDTO;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class CslLogRequestDataDTOTest extends TestCase
{
    public function testPrepareAndGetLogRequestDataWithAllValues(): void
    {
        $dto = new CslLogRequestDataDTO();
        $requestUid = Uuid::uuid7();

        $dto->prepareLogRequestData(
            requestBody: ['foo' => 'bar'],
            resource: '/api/test',
            method: 'POST',
            requestUid: $requestUid,
            ip: ['127.0.0.1']
        );

        $data = $dto->getLogRequestData();

        $this->assertSame(['foo' => 'bar'], $data['requestBody']);
        $this->assertSame('/api/test', $data['resource']);
        $this->assertSame('POST', $data['method']);
        $this->assertInstanceOf(UuidInterface::class, $data['requestUid']);
        $this->assertSame(['127.0.0.1'], $data['ip']);
    }

    public function testPrepareAndGetLogRequestDataWithOptionalValuesAsNull(): void
    {
        $dto = new CslLogRequestDataDTO();

        $dto->prepareLogRequestData(
            requestBody: [],
            resource: '/health',
            method: 'GET'
        );

        $data = $dto->getLogRequestData();

        $this->assertSame([], $data['requestBody']);
        $this->assertSame('/health', $data['resource']);
        $this->assertSame('GET', $data['method']);
        $this->assertNull($data['requestUid']);
        $this->assertNull($data['ip']);
    }
}
