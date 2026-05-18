<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Events;

use CSL\Events\CslResponseInternalSubscriber;
use CSL\Events\DTO\CslEventsSubscriberDTO;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class CslResponseInternalSubscriberTest extends TestCase
{
    public function testSameRequestKeepsIdenticalRequestUidAcrossRepeatedCalls(): void
    {
        $subscriber = $this->createSubscriber();
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();
        $request->attributes->set('requestUid', \Ramsey\Uuid\Uuid::uuid7());

        $response = new Response('original', 200);

        $event1 = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $subscriber->onKernelResponse($event1);
        $uid1 = $request->attributes->get('requestUid');

        $event2 = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $subscriber->onKernelResponse($event2);
        $uid2 = $request->attributes->get('requestUid');

        self::assertSame($uid1, $uid2);
    }

    public function testSubRequestsDoNotTransformResponse(): void
    {
        $subscriber = $this->createSubscriber();
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();
        $response = new Response('original', 200, ['Content-Type' => 'text/plain']);

        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST, $response);
        $subscriber->onKernelResponse($event);

        self::assertSame('original', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('text/plain', $response->headers->get('Content-Type'));
    }

    public function testMainRequestTransformsResponseWhenNotErrorAndSuccessful(): void
    {
        $subscriber = $this->createSubscriber();
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();
        $response = new Response('original', 200);

        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $subscriber->onKernelResponse($event);

        self::assertNotSame('original', $response->getContent());
        self::assertNotNull($response->headers->get('Content-Type'));
    }

    public function testMainRequestDoesNotTransformDocsRoute(): void
    {
        $subscriber = $this->createSubscriber();
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = Request::create('/api/doc');
        $request->attributes->set('_route', 'app.swagger_ui');
        $response = new Response('<html>docs</html>', 200, ['Content-Type' => 'text/html']);

        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $subscriber->onKernelResponse($event);

        self::assertSame('<html>docs</html>', $response->getContent());
        self::assertSame('text/html', $response->headers->get('Content-Type'));
    }

    public function testMainRequestDoesNotTransformWhenErrorHandledFlagIsSet(): void
    {
        $subscriber = $this->createSubscriber();
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();
        $request->attributes->set('_csl_error_handled', true);
        $response = new Response('original', 200);

        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $subscriber->onKernelResponse($event);

        self::assertSame('original', $response->getContent());
    }

    public function testMainRequestDoesNotTransformWhenResponseIsErrorStatus(): void
    {
        $subscriber = $this->createSubscriber();
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();
        $response = new Response('original', 500);

        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $subscriber->onKernelResponse($event);

        self::assertSame('original', $response->getContent());
        self::assertSame(500, $response->getStatusCode());
    }

    public function testMainRequestDoesNotTransformProfilerToolbarStylesheet(): void
    {
        $subscriber = $this->createSubscriber();
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = Request::create('/_wdt/styles');
        $request->attributes->set('_route', '_wdt_stylesheet');
        $response = new Response('.sf-toolbar {}', 200, ['Content-Type' => 'text/css']);

        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $subscriber->onKernelResponse($event);

        self::assertSame('.sf-toolbar {}', $response->getContent());
        self::assertSame('text/css', $response->headers->get('Content-Type'));
    }

    public function testMainRequestDoesNotTransformProfilerToolbarAjaxResponse(): void
    {
        $subscriber = $this->createSubscriber();
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = Request::create('/_wdt/abc123');
        $request->attributes->set('_route', '_wdt');
        $response = new Response('<div class="sf-toolbar">toolbar</div>', 200, ['Content-Type' => 'text/html']);

        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $subscriber->onKernelResponse($event);

        self::assertSame('<div class="sf-toolbar">toolbar</div>', $response->getContent());
        self::assertSame('text/html', $response->headers->get('Content-Type'));
    }

    private function createSubscriber(): CslResponseInternalSubscriber
    {
        $psrLogger = $this->createStub(LoggerInterface::class);
        $dto = $this->createStub(CslEventsSubscriberDTO::class);
        $dto->method('getCslLogger')->willReturn(new \CSL\Module\LoggerBundle\CslLogger\CslLogger($psrLogger));

        return new CslResponseInternalSubscriber($dto);
    }
}
