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
    /**
     * @return array{
     *   debug: array<mixed>,
     *   info: array<mixed>,
     *   notice: array<mixed>,
     * }
     */
    public static function provideInfoMethods(): array
    {
        return [
            'debug' => ['logDebug', 'debug', 'Debug'],
            'info' => ['logInfo', 'info', 'Info'],
            'notice' => ['logNotice', 'notice', 'Notice'],
        ];
    }

    /**
     * @param non-empty-string $method
     * @param non-empty-string $psrMethod
     * @param non-empty-string $message
     */
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
