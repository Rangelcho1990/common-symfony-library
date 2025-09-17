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
    public function testValidateCreateCslLoggerSuccess(): void
    {
        $clsLoggerFactory = $this->getMockBuilder(CslLoggerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(LoggerInterface::class, $clsLoggerFactory->createLogger());
    }
}
