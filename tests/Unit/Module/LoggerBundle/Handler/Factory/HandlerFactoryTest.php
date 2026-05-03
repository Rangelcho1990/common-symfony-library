<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\LoggerBundle\Handler\Factory;

use CSL\Module\LoggerBundle\DTO\LoggerConfigurationDTO;
use CSL\Module\LoggerBundle\Handler\CslHandlerBuilderInterface;
use CSL\Module\LoggerBundle\Handler\Factory\HandlerFactory;
use CSL\Module\LoggerBundle\Handler\Registry\HandlerRegistryInterface;
use Monolog\Handler\HandlerInterface;
use PHPUnit\Framework\TestCase;

class HandlerFactoryTest extends TestCase
{
    public function testCreateHandlerPropagatesLoggerConfigurationToResolvedHandlerBuilder(): void
    {
        $loggerConfiguration = new LoggerConfigurationDTO();
        $loggerConfiguration->prepareConfigurationData('StreamHandler', [
            'level' => 100,
            'format' => 'test',
            'host' => 'php://memory',
            'port' => null,
            'source' => null,
            'ignoreConnectionErrors' => null,
        ]);

        $monologHandler = $this->createStub(HandlerInterface::class);
        $handlerBuilder = $this->createMock(CslHandlerBuilderInterface::class);
        $handlerBuilder
            ->expects(self::once())
            ->method('setLoggerConfiguration')
            ->with(self::identicalTo($loggerConfiguration));
        $handlerBuilder
            ->expects(self::once())
            ->method('getHandler')
            ->willReturn($monologHandler);

        $handlerRegistry = $this->createMock(HandlerRegistryInterface::class);
        $handlerRegistry
            ->expects(self::once())
            ->method('getHandler')
            ->with('CslStreamHandler')
            ->willReturn($handlerBuilder);

        $handlerFactory = new HandlerFactory($handlerRegistry);

        $this->assertSame($monologHandler, $handlerFactory->createHandler($loggerConfiguration));
    }
}
