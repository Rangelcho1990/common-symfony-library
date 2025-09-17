<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\LoggerBundle\Loggers;

use CSL\DTO\Logger\LoggerConfigurationDTO;
use CSL\Module\LoggerBundle\Loggers\CslHandlerInterface;
use CSL\Module\LoggerBundle\Loggers\CslStreamHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

class CslStreamHandlerTest extends TestCase
{
    private LoggerConfigurationDTO $loggerConfigurationDTO;
    private LogRecord $logRecord;

    protected function setUp(): void
    {
        $this->loggerConfigurationDTO = new LoggerConfigurationDTO();
        $data                         = [
            'host'   => 'php://stdout',
            'level'  => 100,
            'format' => 'test',
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

    public function testValidateCslStreamHandlerHandlerInstance(): void
    {
        $cslStreamHandler = $this->getMockBuilder(CslHandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(HandlerInterface::class, $cslStreamHandler->getHandler());
    }

    public function testValidateCslStreamHandlerHandlerBuild(): void
    {
        $cslStreamHandler = new CslStreamHandler();
        $cslStreamHandler->setLoggerConfiguration($this->loggerConfigurationDTO);
        $streamHandler = $cslStreamHandler->getHandler();

        $logger = new Logger('test');
        $logger->pushHandler($streamHandler);

        $this->assertCount(1, $logger->getHandlers());
        $streamHandlerSetup = $logger->getHandlers()[0];

        $this->assertSame($streamHandler, $streamHandlerSetup);
    }

    public function testCslStreamHandlerLogIsHandling(): void
    {
        $cslStreamHandler = new CslStreamHandler();
        $cslStreamHandler->setLoggerConfiguration($this->loggerConfigurationDTO);
        $streamHandler = $cslStreamHandler->getHandler();

        $this->assertTrue($streamHandler->isHandling($this->logRecord));
    }

    public function testCslStreamHandlerLogHandleBatch(): void
    {
        $cslStreamHandler = new CslStreamHandler();
        $cslStreamHandler->setLoggerConfiguration($this->loggerConfigurationDTO);
        $streamHandler = $cslStreamHandler->getHandler();
        $result        = $streamHandler->handleBatch([$this->logRecord, $this->logRecord]);

        $this->assertNull($result);
        unset($result);
    }
}
