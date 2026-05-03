<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\LoggerBundle\Handler;

use CSL\Exceptions\ParameterNotFoundException;
use CSL\Module\LoggerBundle\DTO\LoggerConfigurationDTO;
use CSL\Module\LoggerBundle\Handler\CslGelfHandlerTcp;
use CSL\Module\LoggerBundle\LoggerFormatters\GelfHandlerFormatter;
use Gelf\Publisher;
use Gelf\Transport\IgnoreErrorTransportWrapper;
use Gelf\Transport\TcpTransport;
use Gelf\Transport\TransportInterface;
use Monolog\Handler\GelfHandler;
use Monolog\Level;
use PHPUnit\Framework\TestCase;

class CslGelfHandlerTcpTest extends TestCase
{
    public function testGetHandlerRequiresPort(): void
    {
        $cslGelfHandlerTcp = new CslGelfHandlerTcp();
        $cslGelfHandlerTcp->setLoggerConfiguration($this->createLoggerConfiguration(port: null));

        $this->expectException(ParameterNotFoundException::class);
        $this->expectExceptionMessage('Missing port for CslGelfHandlerTcp!');

        $cslGelfHandlerTcp->getHandler();
    }

    public function testGetHandlerWrapsTransportWhenIgnoreConnectionErrorsIsTrue(): void
    {
        $cslGelfHandlerTcp = new CslGelfHandlerTcp();
        $cslGelfHandlerTcp->setLoggerConfiguration($this->createLoggerConfiguration(
            port: 12201,
            ignoreConnectionErrors: true
        ));

        $handler = $cslGelfHandlerTcp->getHandler();

        $this->assertInstanceOf(GelfHandler::class, $handler);
        $this->assertSame(Level::Info, $handler->getLevel());
        $this->assertInstanceOf(GelfHandlerFormatter::class, $handler->getFormatter());

        $transport = $this->getTransportFromGelfHandler($handler);

        $this->assertInstanceOf(IgnoreErrorTransportWrapper::class, $transport);
        $this->assertInstanceOf(TcpTransport::class, $this->getWrappedTransport($transport));
    }

    private function createLoggerConfiguration(?int $port, ?bool $ignoreConnectionErrors = null): LoggerConfigurationDTO
    {
        $loggerConfigurationDTO = new LoggerConfigurationDTO();
        $loggerConfigurationDTO->prepareConfigurationData('GelfHandlerTcp', [
            'level' => 200,
            'format' => 'test',
            'host' => '127.0.0.1',
            'port' => $port,
            'source' => 'test-source',
            'ignoreConnectionErrors' => $ignoreConnectionErrors,
        ]);

        return $loggerConfigurationDTO;
    }

    private function getTransportFromGelfHandler(GelfHandler $handler): TransportInterface
    {
        $publisherProperty = new \ReflectionProperty(GelfHandler::class, 'publisher');
        $publisher = $publisherProperty->getValue($handler);

        $this->assertInstanceOf(Publisher::class, $publisher);

        $transports = $publisher->getTransports();

        $this->assertCount(1, $transports);

        return $transports[0];
    }

    private function getWrappedTransport(IgnoreErrorTransportWrapper $transport): TransportInterface
    {
        $transportProperty = new \ReflectionProperty(IgnoreErrorTransportWrapper::class, 'transport');
        $wrappedTransport = $transportProperty->getValue($transport);

        $this->assertInstanceOf(TransportInterface::class, $wrappedTransport);

        return $wrappedTransport;
    }
}
