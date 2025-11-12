<?php

declare(strict_types=1);

namespace CSL\DTO\Events;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CslEventsSubscriberDTO
{
    public function __construct(
        private readonly ContainerBagInterface $parameterBag,
        private readonly ValidatorInterface $validator,
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
}
