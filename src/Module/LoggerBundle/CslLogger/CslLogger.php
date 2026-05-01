<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\CslLogger;

use CSL\Module\LoggerBundle\CslLogger\CslLoggerCriticalEvents\CslLoggerCriticalEvents;
use CSL\Module\LoggerBundle\CslLogger\CslLoggerImportedEvents\CslLoggerImportedEvents;
use CSL\Module\LoggerBundle\CslLogger\CslLoggerInfoEvents\CslLoggerInfoEvents;
use Psr\Log\LoggerInterface;

final class CslLogger implements CslLoggerInterface
{
    private CslLoggerCriticalEvents $criticalEvents;
    private CslLoggerInfoEvents $infoEvents;
    private CslLoggerImportedEvents $importedEvents;

    public function __construct(LoggerInterface $logger)
    {
        $this->criticalEvents = new CslLoggerCriticalEvents($logger);
        $this->infoEvents = new CslLoggerInfoEvents($logger);
        $this->importedEvents = new CslLoggerImportedEvents($logger);
    }

    public function getCriticalEvents(): CslLoggerCriticalEvents
    {
        return $this->criticalEvents;
    }

    public function getInfoEvents(): CslLoggerInfoEvents
    {
        return $this->infoEvents;
    }

    public function getImportedEvents(): CslLoggerImportedEvents
    {
        return $this->importedEvents;
    }
}
