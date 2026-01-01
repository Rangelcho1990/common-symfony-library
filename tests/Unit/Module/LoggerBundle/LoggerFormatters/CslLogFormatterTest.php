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

        // Verify timestamp is a string (ISO 8601 format)
        $this->assertArrayHasKey('timestamp', $response);
        $this->assertIsString($response['timestamp']);

        // Verify all top-level fields exist
        $this->assertArrayHasKey('level', $response);
        $this->assertArrayHasKey('messageTemplate', $response);
        $this->assertArrayHasKey('requestUid', $response);
        $this->assertArrayHasKey('requestBody', $response);
        $this->assertArrayHasKey('resource', $response);
        $this->assertArrayHasKey('method', $response);
        $this->assertArrayHasKey('ip', $response);
        $this->assertArrayHasKey('other', $response);
        $this->assertArrayHasKey('responseBody', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertArrayHasKey('file', $response);
        $this->assertArrayHasKey('line', $response);
        $this->assertArrayHasKey('stackTrace', $response);
        $this->assertArrayHasKey('code', $response);

        // Verify message is correctly set
        $this->assertEquals('Hello from test', $response['message']);
    }
}
