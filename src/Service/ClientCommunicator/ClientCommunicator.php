<?php

declare(strict_types=1);

namespace CSL\Service\ClientCommunicator;

class ClientCommunicator implements ClientCommunicatorInterface
{
    /**
     * @var array<string, array{startTime?: float, endTime?: float}>
     */
    private array $timers = [];

    public function startTimer(string $clientId): void
    {
        $this->timers[$clientId]['startTime'] = microtime(true);
    }

    public function stopTimer(string $clientId): void
    {
        if (!isset($this->timers[$clientId]['startTime'])) {
            return;
        }

        $this->timers[$clientId]['endTime'] = microtime(true);
    }

    /**
     * @return array{startTime?: float, endTime?: float, durationMs?: int}
     */
    public function stopAndTakeCommunicationTime(string $clientId): array
    {
        if (!isset($this->timers[$clientId]['startTime'])) {
            $this->clearTimer($clientId);

            return [];
        }

        $timer = $this->timers[$clientId];
        $timer['endTime'] ??= microtime(true);
        $timer['durationMs'] = (int) round(($timer['endTime'] - $timer['startTime']) * 1000);

        $this->clearTimer($clientId);

        return $timer;
    }

    public function clearTimer(string $clientId): void
    {
        unset($this->timers[$clientId]);
    }
}
