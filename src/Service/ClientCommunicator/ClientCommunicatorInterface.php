<?php

declare(strict_types=1);

namespace CSL\Service\ClientCommunicator;

interface ClientCommunicatorInterface
{
    public function startTimer(string $clientId): void;

    public function stopTimer(string $clientId): void;

    public function getCommunicationTime(string $clientId): array;
}
