<?php

declare(strict_types=1);

namespace CSL\Module\Endpoint\Resolver;

interface ControllerClassResolverInterface
{
    /** Extract the configured controller name; Symfony owns controller resolution. */
    public function resolve(string $routeName): string;
}
