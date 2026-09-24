<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Module\Endpoint;

use CSL\Endpoints\Examples\ExampleList\Controller\ExampleController;
use CSL\Module\Endpoint\Resolver\ControllerClassResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

final class ControllerClassResolverTest extends TestCase
{
    public function testUsesNamedRouteAndDoesNotCacheAnotherRoutesController(): void
    {
        $routes = new RouteCollection();
        $routes->add('unrelated_name', new Route('/example', ['_controller' => ExampleController::class.'::example']));
        $routes->add('invokable', new Route('/other', ['_controller' => \stdClass::class]));
        $router = $this->createStub(RouterInterface::class);
        $router->method('getRouteCollection')->willReturn($routes);
        $resolver = new ControllerClassResolver($router);
        self::assertSame(ExampleController::class, $resolver->resolve('unrelated_name'));
        self::assertSame(\stdClass::class, $resolver->resolve('invokable'));
    }

    public function testControllerExistenceIsLeftToSymfony(): void
    {
        $routes = new RouteCollection();
        $routes->add('example', new Route('/example', ['_controller' => 'MissingController::action']));
        $router = $this->createStub(RouterInterface::class);
        $router->method('getRouteCollection')->willReturn($routes);
        self::assertSame('MissingController', (new ControllerClassResolver($router))->resolve('example'));
    }

    /** @return iterable<string, array{string, mixed, bool, string}> */
    public static function invalidRoutes(): iterable
    {
        yield 'unknown route' => ['missing', null, false, 'not registered'];
        yield 'empty route name' => ['', null, false, 'not registered'];
        yield 'missing controller' => ['example', null, true, 'must declare a controller'];
        yield 'unsupported controller' => ['example', [], true, 'must declare a controller'];
    }

    #[DataProvider('invalidRoutes')]
    public function testRejectsInvalidRoutes(string $routeName, mixed $controller, bool $registered, string $message): void
    {
        $routes = new RouteCollection();
        if ($registered) {
            $routes->add($routeName, new Route('/example', ['_controller' => $controller]));
        }
        $router = $this->createStub(RouterInterface::class);
        $router->method('getRouteCollection')->willReturn($routes);
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage($message);
        (new ControllerClassResolver($router))->resolve($routeName);
    }
}
