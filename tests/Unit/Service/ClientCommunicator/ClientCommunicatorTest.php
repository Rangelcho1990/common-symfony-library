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

        self::assertSame([], $clientCommunicator->getCommunicationTime('unknown-client'));
    }

    public function testStopTimerWithoutStartDoesNotCreateCommunicationTime(): void
    {
        $clientCommunicator = new ClientCommunicator();

        $clientCommunicator->stopTimer('client-without-start');

        self::assertSame([], $clientCommunicator->getCommunicationTime('client-without-start'));
    }

    public function testStartTimerStoresStartTimeOnly(): void
    {
        $clientCommunicator = new ClientCommunicator();
        $beforeStart = microtime(true);

        $clientCommunicator->startTimer('client-a');

        $afterStart = microtime(true);
        $communicationTime = $clientCommunicator->getCommunicationTime('client-a');

        if (!isset($communicationTime['startTime'])) {
            self::fail('Expected start time to be stored.');
        }

        self::assertArrayNotHasKey('endTime', $communicationTime);
        self::assertArrayNotHasKey('durationMs', $communicationTime);
        self::assertGreaterThanOrEqual($beforeStart, $communicationTime['startTime']);
        self::assertLessThanOrEqual($afterStart, $communicationTime['startTime']);
    }

    public function testStopTimerStoresEndTimeAndDuration(): void
    {
        $clientCommunicator = new ClientCommunicator();

        $clientCommunicator->startTimer('client-a');
        usleep(1000);
        $clientCommunicator->stopTimer('client-a');

        $communicationTime = $clientCommunicator->getCommunicationTime('client-a');

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
        self::assertSame(
            (int) round(($communicationTime['endTime'] - $communicationTime['startTime']) * 1000),
            $communicationTime['durationMs']
        );
    }

    public function testStoresTimersPerClientId(): void
    {
        $clientCommunicator = new ClientCommunicator();

        $clientCommunicator->startTimer('client-a');
        $clientCommunicator->startTimer('client-b');
        $clientCommunicator->stopTimer('client-a');

        $firstClientTime = $clientCommunicator->getCommunicationTime('client-a');
        $secondClientTime = $clientCommunicator->getCommunicationTime('client-b');

        self::assertArrayHasKey('endTime', $firstClientTime);
        self::assertArrayHasKey('durationMs', $firstClientTime);
        self::assertArrayHasKey('startTime', $secondClientTime);
        self::assertArrayNotHasKey('endTime', $secondClientTime);
        self::assertArrayNotHasKey('durationMs', $secondClientTime);
    }
}
