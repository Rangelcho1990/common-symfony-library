<?php

declare(strict_types=1);

namespace CSL\Module\Endpoint\Resolver;

use Symfony\Component\Routing\RouterInterface;

final class ControllerClassResolver implements ControllerClassResolverInterface
{
    public function __construct(private readonly RouterInterface $router)
    {
    }

    public function resolve(string $routeName): string
    {
        $route = $this->router->getRouteCollection()->get($routeName);
        if (null === $route) {
            throw new \LogicException(sprintf('Cannot validate endpoint: route "%s" is not registered.', $routeName));
        }

        $controller = $route->getDefault('_controller');
        if (!is_string($controller) || '' === $controller) {
            throw new \LogicException(sprintf('Route "%s" must declare a controller class or Class::method.', $routeName));
        }

        return ltrim(explode('::', $controller, 2)[0], '\\');
    }
}
