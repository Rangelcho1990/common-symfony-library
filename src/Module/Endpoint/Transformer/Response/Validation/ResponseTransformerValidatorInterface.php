<?php

declare(strict_types=1);

namespace CSL\Module\Endpoint\Transformer\Response\Validation;

interface ResponseTransformerValidatorInterface
{
    /**
     * Validate the endpoint's response transformer without constructing or executing it.
     *
     * @return class-string the validated transformer class
     *
     * @throws \LogicException when the route or expected class is missing or invalid
     */
    public function validate(string $routeName): string;
}
