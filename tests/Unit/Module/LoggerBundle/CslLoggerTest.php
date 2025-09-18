<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\LoggerBundle;

use CSL\Module\LoggerBundle\CslLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CslLoggerTest extends TestCase
{
    public LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testValidateCslLoggerAddContextWithData(): void
    {
        $cslLogger = new CslLogger($this->logger);
        $cslLogger->addContext([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $this->addToAssertionCount(1);
    }

    public function testValidateCslLoggerAddContextWithEmptyData(): void
    {
        $cslLogger = new CslLogger($this->logger);
        $cslLogger->addContext([]);

        $this->addToAssertionCount(1);
    }

    public function testValidateCslLoggerGetContextWithData(): void
    {
        $cslLogger = new CslLogger($this->logger);
        $data = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $cslLogger->addContext($data);

        $this->assertSame(
            $data,
            $cslLogger->getContext()
        );
    }

    public function testValidateCslLoggerGetContextWithEmptyData(): void
    {
        $cslLogger = new CslLogger($this->logger);
        $cslLogger->addContext([]);

        $this->assertSame([], $cslLogger->getContext());
    }
}
