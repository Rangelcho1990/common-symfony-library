<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\LoggerBundle;

use CSL\Exceptions\NotImplementedException;
use CSL\Module\ErrorHandler\AbstractErrorHandler;
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
                    'format' => 'test',
                    'host' => 'php://memory',
                    'port' => null,
                    'source' => null,
                    'ignoreConnectionErrors' => null,
                ],
            ]);

        $handler = $this->createStub(\Monolog\Handler\HandlerInterface::class);

        $handlerFactory = $this->createStub(HandlerFactoryInterface::class);
        $handlerFactory->method('createHandler')->willReturn($handler);

        $errorHandler = $this->createStub(AbstractErrorHandler::class);

        $clsLoggerFactory = new CslLoggerFactory(
            new Logger('test'),
            $parameterBag,
            $errorHandler,
            $handlerFactory
        );

        $this->assertInstanceOf(LoggerInterface::class, $clsLoggerFactory->createLogger());
    }
}
