<?php

declare(strict_types=1);

namespace CSL\DTO\Events;

use CSL\Module\LoggerBundle\CslLogger\CslLogger;
use CSL\Module\LoggerBundle\CslLoggerFactory;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CslEventsSubscriberDTO
{
    public function __construct(
        private readonly ContainerBagInterface $parameterBag,
        private readonly ValidatorInterface $validator,
        private readonly CslLoggerFactory $loggerFactory,
    ) {
    }

    public function getParameterBag(): ContainerBagInterface
    {
        return $this->parameterBag;
    }

    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    public function getCslLogger(): CslLogger
    {
        return new CslLogger(
            $this->loggerFactory->createLogger()
        );
    }
}
