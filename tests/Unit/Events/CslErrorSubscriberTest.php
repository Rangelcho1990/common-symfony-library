<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Events;

use CSL\Events\CslErrorSubscriber;
use CSL\Events\DTO\CslEventsSubscriberDTO;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class CslErrorSubscriberTest extends TestCase
{
    public function testSameRequestGetsIdenticalRequestUidAcrossRepeatedExceptionHandlingCalls(): void
    {
        $subscriber = $this->createSubscriber();
        $kernel = $this->createStub(HttpKernelInterface::class);

        $request = new Request();

        $event1 = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new \RuntimeException('boom 1')
        );

        $subscriber->onKernelException($event1);

        $uid1 = $request->attributes->get('requestUid');
        self::assertNotNull($uid1, 'requestUid should be set on first exception handling call');

        $event2 = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new \RuntimeException('boom 2')
        );

        $subscriber->onKernelException($event2);

        $uid2 = $request->attributes->get('requestUid');
        self::assertNotNull($uid2, 'requestUid should still be set on second exception handling call');

        self::assertSame($uid1, $uid2, 'Same Request should keep the same requestUid instance across calls');
    }

    public function testSubRequestsDoNotGetCslErrorHandledSet(): void
    {
        $subscriber = $this->createSubscriber();
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();

        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::SUB_REQUEST,
            new \RuntimeException('boom')
        );

        $subscriber->onKernelException($event);

        self::assertFalse(
            $request->attributes->getBoolean('_csl_error_handled'),
            '_csl_error_handled must not be set for sub-requests'
        );
    }

    public function testMainRequestDoesGetCslErrorHandledSet(): void
    {
        $subscriber = $this->createSubscriber();
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();

        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new \RuntimeException('boom')
        );

        $subscriber->onKernelException($event);

        self::assertTrue(
            $request->attributes->getBoolean('_csl_error_handled'),
            '_csl_error_handled must be set for main requests'
        );
    }

    public function testKernelExceptionSetsJsonResponseWith500StatusAndMessage(): void
    {
        $subscriber = $this->createSubscriber();
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();

        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new \RuntimeException('boom', 123)
        );

        $subscriber->onKernelException($event);

        $response = $event->getResponse();
        self::assertInstanceOf(Response::class, $response);
        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        self::assertSame('application/json', $response->headers->get('content-type'));

        $decoded = json_decode((string) $response->getContent(), true);
        self::assertIsArray($decoded);
        self::assertSame('boom', $decoded['message'] ?? null);
        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $decoded['code'] ?? null);
    }

    private function createSubscriber(): CslErrorSubscriber
    {
        $psrLogger = $this->createStub(LoggerInterface::class);

        $dto = $this->createStub(CslEventsSubscriberDTO::class);
        $dto->method('getCslLogger')->willReturn(new \CSL\Module\LoggerBundle\CslLogger\CslLogger($psrLogger));

        return new CslErrorSubscriber($dto);
    }
}
