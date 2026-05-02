<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\LoggerBundle\CslLogger\CslLoggerInfoEvents;

use CSL\Module\LoggerBundle\CslLogger\CslLoggerInfoEvents\CslLoggerInfoEvents;
use CSL\Module\LoggerBundle\DTO\CslLogRequestDataDTOInterface;
use CSL\Module\LoggerBundle\DTO\CslLogTraceDataDTOInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class CslLoggerInfoEventsTest extends TestCase
{
    public static function provideInfoMethods(): iterable
    {
        yield 'debug' => ['logDebug', 'debug', 'Debug'];
        yield 'info' => ['logInfo', 'info', 'Info'];
        yield 'notice' => ['logNotice', 'notice', 'Notice'];
    }

    #[DataProvider('provideInfoMethods')]
    public function testItLogsWithMergedContext(string $method, string $psrMethod, string $message): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method($psrMethod)
            ->with($message, ['req' => 'r', 'trace' => 't']);

        $events = new CslLoggerInfoEvents($logger);

        $requestDto = $this->createStub(CslLogRequestDataDTOInterface::class);
        $requestDto->method('getLogRequestData')->willReturn(['req' => 'r']);

        $traceDto = $this->createStub(CslLogTraceDataDTOInterface::class);
        $traceDto->method('getLogTraceData')->willReturn(['trace' => 't']);

        $events->{$method}($requestDto, $traceDto);
    }
}

