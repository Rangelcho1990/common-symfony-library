<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\LoggerBundle;

use CSL\Exceptions\NotImplementedException;
use CSL\Module\LoggerBundle\CslLoggerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

class CslLoggerFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NotImplementedException
     */
    public function testCreateCslLoggerSuccess(): void
    {
        $clsLoggerFactory = $this->getMockBuilder(CslLoggerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(LoggerInterface::class, $clsLoggerFactory->createLogger());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NotImplementedException
     */
    public function testEmergencyMethodExist(): void
    {
        $clsLoggerFactory = $this->getMockBuilder(CslLoggerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $clsLoggerFactory->createLogger();

        $this->assertTrue(
            method_exists($logger, 'emergency'),
            'Logger must have an emergency() method'
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NotImplementedException
     */
    public function testAlertMethodExist(): void
    {
        $clsLoggerFactory = $this->getMockBuilder(CslLoggerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $clsLoggerFactory->createLogger();

        $this->assertTrue(
            method_exists($logger, 'alert'),
            'Logger must have an alert() method'
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NotImplementedException
     */
    public function testCriticalMethodExist(): void
    {
        $clsLoggerFactory = $this->getMockBuilder(CslLoggerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $clsLoggerFactory->createLogger();

        $this->assertTrue(
            method_exists($logger, 'critical'),
            'Logger must have an critical() method'
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NotImplementedException
     */
    public function testWarningMethodExist(): void
    {
        $clsLoggerFactory = $this->getMockBuilder(CslLoggerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $clsLoggerFactory->createLogger();

        $this->assertTrue(
            method_exists($logger, 'warning'),
            'Logger must have an warning() method'
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NotImplementedException
     */
    public function testNoticeMethodExist(): void
    {
        $clsLoggerFactory = $this->getMockBuilder(CslLoggerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $clsLoggerFactory->createLogger();

        $this->assertTrue(
            method_exists($logger, 'notice'),
            'Logger must have an notice() method'
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NotImplementedException
     */
    public function testInfoMethodExist(): void
    {
        $clsLoggerFactory = $this->getMockBuilder(CslLoggerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $clsLoggerFactory->createLogger();

        $this->assertTrue(
            method_exists($logger, 'info'),
            'Logger must have an info() method'
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NotImplementedException
     */
    public function testDebugMethodExist(): void
    {
        $clsLoggerFactory = $this->getMockBuilder(CslLoggerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $clsLoggerFactory->createLogger();

        $this->assertTrue(
            method_exists($logger, 'debug'),
            'Logger must have an debug() method'
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NotImplementedException
     */
    public function testLogMethodExist(): void
    {
        $clsLoggerFactory = $this->getMockBuilder(CslLoggerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $clsLoggerFactory->createLogger();

        $this->assertTrue(
            method_exists($logger, 'log'),
            'Logger must have an log() method'
        );
    }
}
