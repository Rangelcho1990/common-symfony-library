<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\LoggerBundle\LoggerFormatters;

use CSL\Module\LoggerBundle\LoggerFormatters\GelfHandlerFormatter;
use CSL\Module\LoggerBundle\LoggerFormatters\GelfHandlerFormatterInterface;
use Gelf\Message;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

class GelfHandlerFormatterTest extends TestCase
{
    private LogRecord $logRecord;

    protected function setUp(): void
    {
        $this->logRecord = $this->createLogRecord();
    }

    public function testValidateGelfHandlerFormatterInstance(): void
    {
        $gelfHandlerFormatter = new GelfHandlerFormatter('test-host', null, '');

        $this->assertInstanceOf(GelfMessageFormatter::class, $gelfHandlerFormatter);
        $this->assertInstanceOf(GelfHandlerFormatterInterface::class, $gelfHandlerFormatter);
    }

    public function testValidateGelfHandlerFormatterResponseStructure(): void
    {
        $gelfHandlerFormatter = new GelfHandlerFormatter('test-host', null, '');
        $response = $gelfHandlerFormatter->format($this->logRecord);
        $expectedMessage = '{"foo":"bar","message":"Hello from test"}';

        $this->assertInstanceOf(Message::class, $response);
        $this->assertSame($expectedMessage, $response->getShortMessage());
        $this->assertSame($expectedMessage, $response->getFullMessage());
        $this->assertSame('test-host', $response->getHost());
        $this->assertSame(3, $response->getSyslogLevel());
        $this->assertSame('test', $response->getAdditional('facility'));
        $this->assertSame('bar', $response->getAdditional('foo'));
    }

    public function testValidateGelfHandlerFormatterMessageIsTruncated(): void
    {
        $gelfHandlerFormatter = new GelfHandlerFormatter('test-host', null, '', 20);
        $response = $gelfHandlerFormatter->format($this->logRecord);
        $expectedMessage = substr('{"foo":"bar","message":"Hello from test"}', 0, 20);

        $this->assertSame($expectedMessage, $response->getShortMessage());
        $this->assertSame($expectedMessage, $response->getFullMessage());
    }

    public function testValidateGelfHandlerFormatterReturnsEmptyMessageWhenContextCannotBeEncoded(): void
    {
        $resource = fopen('php://memory', 'r');

        try {
            $gelfHandlerFormatter = new GelfHandlerFormatter('test-host', null, '');
            $response = $gelfHandlerFormatter->format($this->createLogRecord(['resource' => $resource]));

            $this->assertSame('', $response->getShortMessage());
            $this->assertSame('', $response->getFullMessage());
        } finally {
            if (false !== $resource) {
                fclose($resource);
            }
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    private function createLogRecord(array $context = ['foo' => 'bar']): LogRecord
    {
        return new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Error,
            message: 'Hello from test',
            context: $context,
            extra: []
        );
    }
}
