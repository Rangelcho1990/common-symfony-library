<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\LoggerBundle\Handler;

use CSL\Module\LoggerBundle\DTO\LoggerConfigurationDTO;
use CSL\Module\LoggerBundle\Handler\CslAbstractHandlerBuilder;
use Monolog\Handler\HandlerInterface;
use Monolog\Level;
use PHPUnit\Framework\TestCase;

class CslAbstractHandlerBuilderTest extends TestCase
{
    public function testInvalidLogLevelThrowsException(): void
    {
        $builder = new class extends CslAbstractHandlerBuilder {
            public function getHandler(): HandlerInterface
            {
                throw new \LogicException('Not needed for this test');
            }

            public function resolveLogLevel(): Level
            {
                return $this->getLogLevel();
            }
        };

        $loggerConfigurationDTO = new LoggerConfigurationDTO();
        $loggerConfigurationDTO->prepareConfigurationData('StreamHandler', [
            'level' => 350,
            'format' => 'test',
            'host' => 'php://memory',
            'port' => null,
            'source' => null,
            'ignoreConnectionErrors' => null,
        ]);

        $builder->setLoggerConfiguration($loggerConfigurationDTO);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Monolog log level "350"');

        $builder->resolveLogLevel();
    }
}
