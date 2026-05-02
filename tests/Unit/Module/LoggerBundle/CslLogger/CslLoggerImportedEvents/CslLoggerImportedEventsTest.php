<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\LoggerBundle\CslLogger\CslLoggerImportedEvents;

use CSL\Module\LoggerBundle\CslLogger\CslLoggerImportedEvents\CslLoggerImportedEvents;
use CSL\Module\LoggerBundle\DTO\CslLogRequestDataDTOInterface;
use CSL\Module\LoggerBundle\DTO\CslLogTraceDataDTOInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class CslLoggerImportedEventsTest extends TestCase
{
    public static function provideImportedMethods(): iterable
    {
        yield 'emergency' => ['logEmergency', 'emergency', 'Emergency'];
        yield 'alert' => ['logAlert', 'alert', 'Alert'];
    }

    #[DataProvider('provideImportedMethods')]
    public function testItLogsWithMergedContext(string $method, string $psrMethod, string $message): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method($psrMethod)
            ->with($message, ['req' => 'r', 'trace' => 't']);

        $events = new CslLoggerImportedEvents($logger);

        $requestDto = $this->createStub(CslLogRequestDataDTOInterface::class);
        $requestDto->method('getLogRequestData')->willReturn(['req' => 'r']);

        $traceDto = $this->createStub(CslLogTraceDataDTOInterface::class);
        $traceDto->method('getLogTraceData')->willReturn(['trace' => 't']);

        $events->{$method}($requestDto, $traceDto);
    }
}

