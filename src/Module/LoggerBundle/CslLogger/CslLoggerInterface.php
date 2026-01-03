<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\CslLogger;

use CSL\Module\LoggerBundle\CslLogger\CslLoggerCriticalEvents\CslLoggerCriticalEvents;
use CSL\Module\LoggerBundle\CslLogger\CslLoggerInfoEvents\CslLoggerInfoEvents;
use CSL\Module\LoggerBundle\CslLogger\CslLoggerImportedEvents\CslLoggerImportedEvents;

interface CslLoggerInterface
{
    public function getCriticalEvents(): CslLoggerCriticalEvents;

    public function getInfoEvents(): CslLoggerInfoEvents;

    public function getImportedEvents(): CslLoggerImportedEvents;
}
