<?php

declare(strict_types=1);

namespace CSL\Tests\Functional\Logger;

use CSL\Events\CslErrorSubscriber;
use CSL\Events\CslRequestClientSubscriber;
use CSL\Events\CslResponseClientSubscriber;
use CSL\Events\CslResponseInternalSubscriber;
use CSL\Events\DTO\CslEventsSubscriberDTO;
use CSL\Module\LoggerBundle\CslLogger\CslLoggerInterface;
use CSL\Service\ClientCommunicator\ClientCommunicator;
use CSL\Tests\Functional\KernelTestCaseBase;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class CslLoggerServiceTest extends KernelTestCaseBase
{
    public function testRepeatedSubscriberConstructionPreservesLoggingPipeline(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $defaultLogger = $container->get('monolog.logger');
        $doctrineLogger = $container->get('monolog.logger.doctrine');
        self::assertInstanceOf(Logger::class, $defaultLogger);
        self::assertInstanceOf(Logger::class, $doctrineLogger);
        $defaultHandlers = $defaultLogger->getHandlers();
        $doctrineHandlers = $doctrineLogger->getHandlers();
        $defaultProcessors = $defaultLogger->getProcessors();

        $dto = $container->get(CslEventsSubscriberDTO::class);
        self::assertInstanceOf(CslEventsSubscriberDTO::class, $dto);
        self::assertSame($container->get(CslLoggerInterface::class), $dto->getCslLogger());
        $logger = $container->get('csl.logger');
        self::assertInstanceOf(Logger::class, $logger);
        self::assertNotSame($defaultLogger, $logger);
        self::assertSame('csl', $logger->getName());
        self::assertCount(1, $logger->getHandlers());
        $configuredHandlers = $logger->getHandlers();
        // Stop propagation to stdout while observing real subscriber log records.
        $capture = new TestHandler(bubble: false);
        $logger->pushHandler($capture);
        $communicator = new ClientCommunicator();
        $kernel = $this->createStub(HttpKernelInterface::class);

        for ($i = 0; $i < 3; ++$i) {
            new CslRequestClientSubscriber($dto, $communicator);
            new CslResponseClientSubscriber($dto, $communicator);
            new CslResponseInternalSubscriber($dto);
            $subscriber = new CslErrorSubscriber($dto);
            $subscriber->onKernelException(new ExceptionEvent(
                $kernel,
                Request::create('/example'),
                HttpKernelInterface::MAIN_REQUEST,
                new \RuntimeException('Test error'),
            ));

            self::assertSame($logger, $container->get('csl.logger'));
            self::assertSame([$capture, ...$configuredHandlers], $logger->getHandlers());
            self::assertCount($i + 1, $capture->getRecords());
        }

        self::assertSame($defaultHandlers, $defaultLogger->getHandlers());
        self::assertSame($defaultProcessors, $defaultLogger->getProcessors());
        self::assertSame($doctrineHandlers, $doctrineLogger->getHandlers());
    }
}
