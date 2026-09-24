<?php

declare(strict_types=1);

namespace CSL\Tests\Functional\Endpoint;

use CSL\Endpoints\Examples\ExampleList\Controller\Transformer\Response\ExampleTransformer;
use CSL\Events\CslResponseInternalSubscriber;
use CSL\Module\Endpoint\Transformer\Response\Provider\ResponseTransformerProviderInterface;
use CSL\Module\Endpoint\Transformer\Response\Validation\ResponseTransformerValidatorInterface;
use CSL\Tests\Functional\KernelTestCaseBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;

final class ResponseTransformerValidatorTest extends KernelTestCaseBase
{
    public function testValidatesTransformerForRealMatchedRoute(): void
    {
        $kernel = self::bootKernel();
        $router = self::getContainer()->get('router');
        self::assertInstanceOf(RouterInterface::class, $router);
        $request = Request::create('/example');
        $matched = $router->match($request->getPathInfo());
        $request->attributes->set('_route', $matched['_route']);
        $routeName = $request->attributes->get('_route');
        self::assertIsString($routeName);
        $validator = self::getContainer()->get(ResponseTransformerValidatorInterface::class);
        self::assertInstanceOf(ResponseTransformerValidatorInterface::class, $validator);
        self::assertSame(ExampleTransformer::class, $validator->validate($routeName));
        $provider = self::getContainer()->get(ResponseTransformerProviderInterface::class);
        self::assertInstanceOf(ResponseTransformerProviderInterface::class, $provider);
        self::assertInstanceOf(ExampleTransformer::class, $provider->get(ExampleTransformer::class));
        $subscriber = self::getContainer()->get(CslResponseInternalSubscriber::class);
        self::assertInstanceOf(CslResponseInternalSubscriber::class, $subscriber);
        $response = new Response('original');
        $subscriber->onKernelResponse(new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response));
        self::assertSame('{"success":true,"data":{"id":1,"name":"Example"}}', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
    }
}
