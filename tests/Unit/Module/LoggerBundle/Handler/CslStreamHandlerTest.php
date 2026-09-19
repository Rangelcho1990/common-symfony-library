<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\LoggerBundle\Handler;

use CSL\Module\LoggerBundle\DTO\LoggerConfigurationDTO;
use CSL\Module\LoggerBundle\Handler\CslHandlerBuilderInterface;
use CSL\Module\LoggerBundle\Handler\CslStreamHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
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
        $data = [
            'level' => 100,
            'host' => 'php://memory',
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

    public function testValidateCslStreamHandlerHandlerInstance(): void
    {
        $cslStreamHandler = new CslStreamHandler();
        $cslStreamHandler->setLoggerConfiguration($this->loggerConfigurationDTO);

        $this->assertInstanceOf(CslHandlerBuilderInterface::class, $cslStreamHandler);
        $this->assertInstanceOf(HandlerInterface::class, $cslStreamHandler->getHandler());
    }

    public function testHandlerWritesCanonicalJsonWithoutFormatConfiguration(): void
    {
        $builder = new CslStreamHandler();
        $builder->setLoggerConfiguration($this->loggerConfigurationDTO);
        $handler = $builder->getHandler();
        $this->assertInstanceOf(StreamHandler::class, $handler);
        $handler->handle($this->logRecord);

        $stream = $handler->getStream();
        $this->assertIsResource($stream);
        rewind($stream);
        $output = stream_get_contents($stream);
        $this->assertIsString($output);
        $this->assertStringEndsWith(PHP_EOL, $output);
        $data = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($data);
        $this->assertSame('Hello from test', $data['message']);
        $this->assertSame('ERROR', $data['level']);
        $this->assertSame(400, $data['code']);
    }

    public function testGetHandlerPreservesInvalidLogLevelException(): void
    {
        $loggerConfigurationDTO = new LoggerConfigurationDTO();
        $loggerConfigurationDTO->prepareConfigurationData('StreamHandler', [
            'level' => 350,
            'host' => 'php://memory',
            'port' => null,
            'source' => null,
            'ignoreConnectionErrors' => null,
        ]);

        $cslStreamHandler = new CslStreamHandler();
        $cslStreamHandler->setLoggerConfiguration($loggerConfigurationDTO);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Monolog log level "350"');

        $cslStreamHandler->getHandler();
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

    public function testValidateCslStreamHandlerLogIsHandling(): void
    {
        $cslStreamHandler = new CslStreamHandler();
        $cslStreamHandler->setLoggerConfiguration($this->loggerConfigurationDTO);
        $streamHandler = $cslStreamHandler->getHandler();

        $this->assertTrue($streamHandler->isHandling($this->logRecord));
    }

    public function testValidateCslStreamHandlerLogHandleBatch(): void
    {
        $cslStreamHandler = new CslStreamHandler();
        $cslStreamHandler->setLoggerConfiguration($this->loggerConfigurationDTO);
        $streamHandler = $cslStreamHandler->getHandler();
        $streamHandler->handleBatch([$this->logRecord, $this->logRecord]);

        $this->addToAssertionCount(1);
    }

    public function testValidateCslStreamHandlerLogHandle(): void
    {
        $cslStreamHandler = new CslStreamHandler();
        $cslStreamHandler->setLoggerConfiguration($this->loggerConfigurationDTO);
        $streamHandler = $cslStreamHandler->getHandler();
        $streamHandlerResponse = $streamHandler->handle($this->logRecord);

        $this->assertFalse($streamHandlerResponse); // check handle method for more description
    }
}
