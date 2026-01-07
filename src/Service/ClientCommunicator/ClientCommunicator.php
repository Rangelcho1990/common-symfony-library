<?php

declare(strict_types=1);

namespace CSL\Service\ClientCommunicator;

class ClientCommunicator implements ClientCommunicatorInterface
{
    private array $timers = [];

    public function startTimer(string $clientId): void
    {
        $this->timers[$clientId]['startTime'] = microtime(true);
    }

    public function stopTimer(string $clientId): void
    {
        $this->timers[$clientId]['endTime'] = microtime(true);
    }

    public function getCommunicationTime(string $clientId): array
    {
        if (!isset($this->timers[$clientId])) {
            return [];
        }

        return $this->timers[$clientId];
    }
}
