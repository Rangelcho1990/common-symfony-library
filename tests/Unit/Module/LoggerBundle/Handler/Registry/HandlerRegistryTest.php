<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\LoggerBundle\Handler\Registry;

use CSL\Module\LoggerBundle\Handler\CslHandlerBuilderInterface;
use CSL\Module\LoggerBundle\Handler\Registry\HandlerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class HandlerRegistryTest extends TestCase
{
    public function testGetHandlerReturnsRegisteredHandler(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::never())
            ->method('has');

        $handler = $this->createStub(CslHandlerBuilderInterface::class);
        $registry = new HandlerRegistry($container);
        $registry->registerHandler('CslStreamHandler', $handler);

        $this->assertSame($handler, $registry->getHandler('CslStreamHandler'));
    }

    public function testGetHandlerResolvesContainerServiceAndCachesIt(): void
    {
        $handler = $this->createStub(CslHandlerBuilderInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::once())
            ->method('has')
            ->with('CslStreamHandler')
            ->willReturn(true);
        $container
            ->expects(self::once())
            ->method('get')
            ->with('CslStreamHandler')
            ->willReturn($handler);

        $registry = new HandlerRegistry($container);

        $this->assertSame($handler, $registry->getHandler('CslStreamHandler'));
        $this->assertSame($handler, $registry->getHandler('CslStreamHandler'));
    }

    public function testGetHandlerRejectsContainerServiceThatIsNotAHandlerBuilder(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::once())
            ->method('has')
            ->with('CslStreamHandler')
            ->willReturn(true);
        $container
            ->expects(self::once())
            ->method('get')
            ->with('CslStreamHandler')
            ->willReturn(new \stdClass());

        $registry = new HandlerRegistry($container);

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Handler "CslStreamHandler" must implement CslHandlerBuilderInterface');

        $registry->getHandler('CslStreamHandler');
    }
}
