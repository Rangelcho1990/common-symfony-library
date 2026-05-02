<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\LoggerBundle\CslLogger;

use CSL\Module\LoggerBundle\CslLogger\CslLogger;
use CSL\Module\LoggerBundle\CslLogger\CslLoggerCriticalEvents\CslLoggerCriticalEvents;
use CSL\Module\LoggerBundle\CslLogger\CslLoggerImportedEvents\CslLoggerImportedEvents;
use CSL\Module\LoggerBundle\CslLogger\CslLoggerInfoEvents\CslLoggerInfoEvents;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class CslLoggerTest extends TestCase
{
    public function testItExposesEventGroupsAndReusesInstances(): void
    {
        $logger = $this->createStub(LoggerInterface::class);
        $cslLogger = new CslLogger($logger);

        $critical1 = $cslLogger->getCriticalEvents();
        $critical2 = $cslLogger->getCriticalEvents();
        self::assertInstanceOf(CslLoggerCriticalEvents::class, $critical1);
        self::assertSame($critical1, $critical2);

        $info1 = $cslLogger->getInfoEvents();
        $info2 = $cslLogger->getInfoEvents();
        self::assertInstanceOf(CslLoggerInfoEvents::class, $info1);
        self::assertSame($info1, $info2);

        $imported1 = $cslLogger->getImportedEvents();
        $imported2 = $cslLogger->getImportedEvents();
        self::assertInstanceOf(CslLoggerImportedEvents::class, $imported1);
        self::assertSame($imported1, $imported2);
    }
}
