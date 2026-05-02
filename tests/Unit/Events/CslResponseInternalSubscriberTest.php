<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Events;

use CSL\DTO\Events\CslEventsSubscriberDTO;
use CSL\Events\CslResponseInternalSubscriber;
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
        $kernel = $this->createMock(HttpKernelInterface::class);
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
        $kernel = $this->createMock(HttpKernelInterface::class);
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
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $response = new Response('original', 200);

        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $subscriber->onKernelResponse($event);

        self::assertNotSame('original', $response->getContent());
        self::assertNotNull($response->headers->get('Content-Type'));
    }

    public function testMainRequestDoesNotTransformWhenErrorHandledFlagIsSet(): void
    {
        $subscriber = $this->createSubscriber();
        $kernel = $this->createMock(HttpKernelInterface::class);
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
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $response = new Response('original', 500);

        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $subscriber->onKernelResponse($event);

        self::assertSame('original', $response->getContent());
        self::assertSame(500, $response->getStatusCode());
    }

    private function createSubscriber(): CslResponseInternalSubscriber
    {
        $psrLogger = $this->createMock(LoggerInterface::class);
        $dto = $this->createMock(CslEventsSubscriberDTO::class);
        $dto->method('getCslLogger')->willReturn(new \CSL\Module\LoggerBundle\CslLogger\CslLogger($psrLogger));

        return new CslResponseInternalSubscriber($dto);
    }
}
