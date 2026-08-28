<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\LoggerBundle\DTO;

use CSL\Module\LoggerBundle\DTO\CslLogTraceDataDTO;
use PHPUnit\Framework\TestCase;

class CslLogTraceDataDTOTest extends TestCase
{
    public function testPrepareAndGetLogTraceDataWithAllValues(): void
    {
        $dto = new CslLogTraceDataDTO();

        $dto->prepareLogTraceData(
            messageTemplate: 'template',
            communicationTime: [
                'startTime' => 1.0,
                'endTime' => 1.25,
                'durationMs' => 250,
            ],
            responseBody: '{"ok":true}',
            message: 'Something happened',
            file: '/tmp/example.php',
            line: 42,
            stackTrace: ['trace 1', 'trace 2'],
            code: 500
        );

        $data = $dto->getLogTraceData();

        $this->assertSame('template', $data['messageTemplate']);
        $this->assertSame(
            ['startTime' => 1.0, 'endTime' => 1.25, 'durationMs' => 250],
            $data['communicationTime']
        );
        $this->assertSame('{"ok":true}', $data['responseBody']);
        $this->assertSame('Something happened', $data['message']);
        $this->assertSame('/tmp/example.php', $data['file']);
        $this->assertSame(42, $data['line']);
        $this->assertSame(['trace 1', 'trace 2'], $data['stackTrace']);
        $this->assertSame(500, $data['code']);
    }

    public function testPrepareAndGetLogTraceDataWithOptionalValuesAsNull(): void
    {
        $dto = new CslLogTraceDataDTO();

        $dto->prepareLogTraceData(messageTemplate: 'minimal');

        $data = $dto->getLogTraceData();

        $this->assertSame('minimal', $data['messageTemplate']);
        $this->assertNull($data['communicationTime']);
        $this->assertNull($data['responseBody']);
        $this->assertNull($data['message']);
        $this->assertNull($data['file']);
        $this->assertNull($data['line']);
        $this->assertNull($data['stackTrace']);
        $this->assertNull($data['code']);
    }
}
