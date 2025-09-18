<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\LoggerBundle\LoggerFormatters;

use CSL\DTO\Logger\LoggerConfigurationDTO;
use CSL\Module\LoggerBundle\LoggerFormatters\CslLogFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

class CslLogFormatterTest extends TestCase
{
    private LoggerConfigurationDTO $loggerConfigurationDTO;
    private LogRecord $logRecord;

    protected function setUp(): void
    {
        $this->loggerConfigurationDTO = new LoggerConfigurationDTO();
        $data = [
            'level' => 100,
            'format' => 'test',
            'host' => 'php://stdout',
            'port' => null,
            'source' => null,
            'ignoreConnectionErrors' => null,
        ];
        $this->loggerConfigurationDTO->prepareConfigurationData('StreamHandler', $data);

        $this->logRecord = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Error,
            message: 'Hello from test',
            context: ['foo' => 'bar'],
            extra: []
        );
    }

    public function testValidateCslLogFormatterInstance(): void
    {
        $cslLogFormatter = $this->getMockBuilder(CslLogFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(LineFormatter::class, $cslLogFormatter);
    }

    public function testValidateCslLogFormatterResponseStructure(): void
    {
        $cslLogFormatter = new CslLogFormatter($this->loggerConfigurationDTO->getFormat());
        $response = $cslLogFormatter->format($this->logRecord);
        $response = json_decode($response, true);

        $this->assertIsArray($response);

        $this->assertArrayHasKey('timestamp', $response);
        $this->assertArrayHasKey('date', $response['timestamp']);
        $this->assertArrayHasKey('timezone_type', $response['timestamp']);
        $this->assertArrayHasKey('timezone', $response['timestamp']);

        $this->assertArrayHasKey('level', $response);
        $this->assertArrayHasKey('messageTemplate', $response);
        $this->assertArrayHasKey('additional_data', $response);

        $this->assertArrayHasKey('requestUid', $response['additional_data']);
        $this->assertArrayHasKey('requestBody', $response['additional_data']);
        $this->assertArrayHasKey('resource', $response['additional_data']);
        $this->assertArrayHasKey('method', $response['additional_data']);
        $this->assertArrayHasKey('ip', $response['additional_data']);
        $this->assertArrayHasKey('other', $response['additional_data']);
        $this->assertArrayHasKey('responseBody', $response['additional_data']);
        $this->assertArrayHasKey('message', $response['additional_data']);
        $this->assertArrayHasKey('file', $response['additional_data']);
        $this->assertArrayHasKey('line', $response['additional_data']);
        $this->assertArrayHasKey('stackTrace', $response['additional_data']);
        $this->assertArrayHasKey('code', $response['additional_data']);
    }
}
