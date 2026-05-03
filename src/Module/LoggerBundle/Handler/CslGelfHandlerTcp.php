<?php

declare(strict_types=1);

namespace CSL\Module\LoggerBundle\Handler;

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
        if (null === $this->getLoggerConfiguration()->getPort()) {
            throw new ParameterNotFoundException('Missing port for CslGelfHandlerTcp!');
        }

        $transporter = new TcpTransport(
            $this->getLoggerConfiguration()->getHost(),
            $this->getLoggerConfiguration()->getPort(),
        );

        if (null !== $this->getLoggerConfiguration()->getIgnoreConnectionErrors()
            && $this->getLoggerConfiguration()->getIgnoreConnectionErrors()
        ) {
            $transporter = new IgnoreErrorTransportWrapper($transporter);
        }

        $gelfHandler = new GelfHandler(
            new Publisher($transporter),
            $this->getLogLevel(),
        );

        $gelfHandler->setFormatter(new GelfHandlerFormatter(
            $this->getLoggerConfiguration()->getSource(),
            null,
            ''
        ));

        return $gelfHandler;
    }
}
