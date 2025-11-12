<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Loggers;

use CSL\Exceptions\ParameterNotFoundException;
use CSL\Module\LoggerBundle\LoggerFormatters\GelfHandlerFormatter;
use Gelf\Publisher;
use Gelf\Transport\IgnoreErrorTransportWrapper;
use Gelf\Transport\TcpTransport;
use Monolog\Handler\GelfHandler;
use Monolog\Handler\HandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(id: 'CslGelfHandlerTcp', public: true)]
final class CslGelfHandlerTcp extends CslAbstractHandlerBuilder
{
    /**
     * @throws ParameterNotFoundException
     */
    public function getHandler(): HandlerInterface
    {
        if (null === $this->loggerConfiguration->getPort()) {
            throw new ParameterNotFoundException('Missing port for CslGelfHandlerTcp!');
        }

        $transporter = new TcpTransport(
            $this->loggerConfiguration->getHost(),
            $this->loggerConfiguration->getPort(),
        );

        if (null !== $this->loggerConfiguration->getIgnoreConnectionErrors()
            && $this->loggerConfiguration->getIgnoreConnectionErrors()
        ) {
            $transporter = new IgnoreErrorTransportWrapper($transporter);
        }

        $gelfHandler = new GelfHandler(
            new Publisher($transporter),
            $this->getLogLevel(),
        );

        $gelfHandler->setFormatter(new GelfHandlerFormatter(
            $this->loggerConfiguration->getSource(),
            null,
            ''
        ));

        return $gelfHandler;
    }
}
