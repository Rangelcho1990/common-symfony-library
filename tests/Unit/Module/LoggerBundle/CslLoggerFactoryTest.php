<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\LoggerBundle;

use CSL\Exceptions\NotImplementedException;
use CSL\Module\LoggerBundle\CslLoggerFactory;
use CSL\Module\LoggerBundle\Handler\Factory\HandlerFactoryInterface;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class CslLoggerFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NotImplementedException
     */
    public function testValidateCreateCslLoggerSuccess(): void
    {
        $parameterBag = $this->createMock(ContainerBagInterface::class);
        $parameterBag
            ->expects(self::once())
            ->method('get')
            ->with('handlers')
            ->willReturn([
                'StreamHandler' => [
                    'level' => 100,
                    'host' => 'php://memory',
                    'port' => null,
                    'source' => null,
                    'ignoreConnectionErrors' => null,
                ],
            ]);

        $handler = $this->createStub(\Monolog\Handler\HandlerInterface::class);

        $handlerFactory = $this->createStub(HandlerFactoryInterface::class);
        $handlerFactory->method('createHandler')->willReturn($handler);

        $clsLoggerFactory = new CslLoggerFactory(
            $parameterBag,
            $handlerFactory
        );

        $errorHandler = static fn (): bool => false;
        $exceptionHandler = static function (\Throwable $exception): void {};
        set_error_handler($errorHandler);
        set_exception_handler($exceptionHandler);

        try {
            $logger = $clsLoggerFactory->createLogger();
            self::assertInstanceOf(LoggerInterface::class, $logger);
            self::assertInstanceOf(Logger::class, $logger);
            self::assertSame('csl', $logger->getName());
            self::assertSame([$handler], $logger->getHandlers());
            $currentErrorHandler = set_error_handler($errorHandler);
            restore_error_handler();
            $currentExceptionHandler = set_exception_handler($exceptionHandler);
            restore_exception_handler();
            self::assertSame($errorHandler, $currentErrorHandler);
            self::assertSame($exceptionHandler, $currentExceptionHandler);
        } finally {
            restore_exception_handler();
            restore_error_handler();
        }
    }
}
