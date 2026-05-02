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

    public function getCommunicationTime(string $clientId): array
    {
        if (!isset($this->timers[$clientId])) {
            return [];
        }

        $timer = $this->timers[$clientId];
        if (isset($timer['startTime'], $timer['endTime'])) {
            $timer['durationMs'] = (int) round(($timer['endTime'] - $timer['startTime']) * 1000);
        }

        return $timer;
    }
}
