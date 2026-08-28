<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Service\ClientCommunicator;

use CSL\Service\ClientCommunicator\ClientCommunicator;
use CSL\Service\ClientCommunicator\ClientCommunicatorInterface;
use PHPUnit\Framework\TestCase;

final class ClientCommunicatorTest extends TestCase
{
    public function testValidateClientCommunicatorInstance(): void
    {
        $clientCommunicator = new ClientCommunicator();

        self::assertInstanceOf(ClientCommunicatorInterface::class, $clientCommunicator);
    }

    public function testReturnsEmptyCommunicationTimeForUnknownClient(): void
    {
        $clientCommunicator = new ClientCommunicator();

        self::assertSame([], $clientCommunicator->stopAndTakeCommunicationTime('unknown-client'));
    }

    public function testStopTimerWithoutStartDoesNotCreateCommunicationTime(): void
    {
        $clientCommunicator = new ClientCommunicator();

        $clientCommunicator->stopTimer('client-without-start');

        self::assertSame([], $clientCommunicator->stopAndTakeCommunicationTime('client-without-start'));
    }

    public function testStartTimerStoresStartTime(): void
    {
        $clientCommunicator = new ClientCommunicator();
        $beforeStart = microtime(true);

        $clientCommunicator->startTimer('client-a');

        $afterStart = microtime(true);
        $communicationTime = $clientCommunicator->stopAndTakeCommunicationTime('client-a');

        if (!isset($communicationTime['startTime'])) {
            self::fail('Expected start time to be stored.');
        }

        self::assertGreaterThanOrEqual($beforeStart, $communicationTime['startTime']);
        self::assertLessThanOrEqual($afterStart, $communicationTime['startTime']);
    }

    public function testStopTimerStoresEndTimeAndDuration(): void
    {
        $clientCommunicator = new ClientCommunicator();

        $clientCommunicator->startTimer('client-a');
        usleep(1000);
        $clientCommunicator->stopTimer('client-a');
        $afterStop = microtime(true);
        usleep(1000);

        $communicationTime = $clientCommunicator->stopAndTakeCommunicationTime('client-a');

        if (!isset($communicationTime['startTime'])) {
            self::fail('Expected start time to be stored.');
        }

        if (!isset($communicationTime['endTime'])) {
            self::fail('Expected end time to be stored.');
        }

        if (!isset($communicationTime['durationMs'])) {
            self::fail('Expected duration to be calculated.');
        }

        self::assertGreaterThanOrEqual($communicationTime['startTime'], $communicationTime['endTime']);
        self::assertLessThanOrEqual($afterStop, $communicationTime['endTime']);
        self::assertSame(
            (int) round(($communicationTime['endTime'] - $communicationTime['startTime']) * 1000),
            $communicationTime['durationMs']
        );
    }

    public function testStopAndTakeCommunicationTimeReturnsCompletedTimerAndRemovesIt(): void
    {
        $clientCommunicator = new ClientCommunicator();

        $clientCommunicator->startTimer('client-a');
        usleep(1000);

        $communicationTime = $clientCommunicator->stopAndTakeCommunicationTime('client-a');

        self::assertArrayHasKey('startTime', $communicationTime);
        self::assertArrayHasKey('endTime', $communicationTime);
        self::assertArrayHasKey('durationMs', $communicationTime);
        self::assertSame([], $clientCommunicator->stopAndTakeCommunicationTime('client-a'));
    }

    public function testStopAndTakeCommunicationTimePreservesOtherClientTimers(): void
    {
        $clientCommunicator = new ClientCommunicator();

        $clientCommunicator->startTimer('client-a');
        $clientCommunicator->startTimer('client-b');

        $firstClientTime = $clientCommunicator->stopAndTakeCommunicationTime('client-a');
        $secondClientTime = $clientCommunicator->stopAndTakeCommunicationTime('client-b');

        self::assertArrayHasKey('startTime', $firstClientTime);
        self::assertArrayHasKey('startTime', $secondClientTime);
        self::assertSame([], $clientCommunicator->stopAndTakeCommunicationTime('client-a'));
        self::assertSame([], $clientCommunicator->stopAndTakeCommunicationTime('client-b'));
    }

    public function testClearTimerRemovesStartedTimer(): void
    {
        $clientCommunicator = new ClientCommunicator();
        $clientCommunicator->startTimer('client-a');

        $clientCommunicator->clearTimer('client-a');

        self::assertSame([], $clientCommunicator->stopAndTakeCommunicationTime('client-a'));
    }
}
