<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\LoggerBundle\CslLogger\CslLoggerCriticalEvents;

use CSL\Module\LoggerBundle\CslLogger\CslLoggerCriticalEvents\CslLoggerCriticalEvents;
use CSL\Module\LoggerBundle\DTO\CslLogRequestDataDTOInterface;
use CSL\Module\LoggerBundle\DTO\CslLogTraceDataDTOInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class CslLoggerCriticalEventsTest extends TestCase
{
    /**
     * @return array{
     *   critical: array<mixed>,
     *   error: array<mixed>,
     *   warning: array<mixed>,
     * }
     */
    public static function provideCriticalMethods(): iterable
    {
        return [
            'critical' => ['logCritical', 'critical', 'Critical'],
            'error' => ['logError', 'error', 'Error'],
            'warning' => ['logWarning', 'warning', 'Warning'],
        ];
    }

    /**
     * @param non-empty-string $method
     * @param non-empty-string $psrMethod
     * @param non-empty-string $message
     */
    #[DataProvider('provideCriticalMethods')]
    public function testItLogsWithMergedContext(string $method, string $psrMethod, string $message): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method($psrMethod)
            ->with($message, ['req' => 'r', 'trace' => 't']);

        $events = new CslLoggerCriticalEvents($logger);

        $requestDto = $this->createStub(CslLogRequestDataDTOInterface::class);
        $requestDto->method('getLogRequestData')->willReturn(['req' => 'r']);

        $traceDto = $this->createStub(CslLogTraceDataDTOInterface::class);
        $traceDto->method('getLogTraceData')->willReturn(['trace' => 't']);

        $events->{$method}($requestDto, $traceDto);
    }
}
