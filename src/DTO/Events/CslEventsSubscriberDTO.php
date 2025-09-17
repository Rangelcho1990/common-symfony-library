<?php

declare(strict_types=1);

namespace CSL\DTO\Events;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CslEventsSubscriberDTO
{
    private ContainerBagInterface $parameterBag;
    private ValidatorInterface $validator;

    public function __construct(
        ContainerBagInterface $parameterBag,
        ValidatorInterface $validator,
    ) {
        $this->parameterBag = $parameterBag;
        $this->validator = $validator;
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
