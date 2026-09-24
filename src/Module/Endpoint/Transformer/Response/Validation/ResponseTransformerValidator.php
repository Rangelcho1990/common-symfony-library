<?php

declare(strict_types=1);

namespace CSL\Module\Endpoint\Transformer\Response\Validation;

use CSL\Module\Endpoint\Resolver\ControllerClassResolverInterface;

final class ResponseTransformerValidator implements ResponseTransformerValidatorInterface
{
    public function __construct(private readonly ControllerClassResolverInterface $controllerResolver)
    {
    }

    public function validate(string $routeName): string
    {
        $controller = $this->controllerResolver->resolve($routeName);
        $separator = strrpos($controller, '\\');
        $namespace = false === $separator ? '' : substr($controller, 0, $separator);
        $name = false === $separator ? $controller : substr($controller, $separator + 1);

        if (!str_starts_with($namespace, 'CSL\\Endpoints\\')
            || !str_ends_with($namespace, '\\Controller')
            || !str_ends_with($name, 'Controller')
            || 'Controller' === $name
        ) {
            throw new \LogicException(sprintf('Controller "%s" for route "%s" must follow CSL\\Endpoints\\...\\Controller\\<Name>Controller.', $controller, $routeName));
        }

        $transformer = $namespace.'\\Transformer\\Response\\'.substr($name, 0, -strlen('Controller')).'Transformer';
        if (!class_exists($transformer)) {
            throw new \LogicException(sprintf('Response transformer "%s" required by route "%s" does not exist.', $transformer, $routeName));
        }

        if ((new \ReflectionClass($transformer))->isAbstract()) {
            throw new \LogicException(sprintf('Response transformer "%s" required by route "%s" must be a concrete class.', $transformer, $routeName));
        }

        return $transformer;
    }
}
