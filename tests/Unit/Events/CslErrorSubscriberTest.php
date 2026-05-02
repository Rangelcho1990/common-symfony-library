<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Events;

use CSL\DTO\Events\CslEventsSubscriberDTO;
use CSL\Events\CslErrorSubscriber;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class CslErrorSubscriberTest extends TestCase
{
    public function testSameRequestGetsIdenticalRequestUidAcrossRepeatedExceptionHandlingCalls(): void
    {
        $subscriber = $this->createSubscriber();
        $kernel = $this->createMock(HttpKernelInterface::class);

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
        $kernel = $this->createMock(HttpKernelInterface::class);
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
        $kernel = $this->createMock(HttpKernelInterface::class);
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

    private function createSubscriber(): CslErrorSubscriber
    {
        $psrLogger = $this->createMock(LoggerInterface::class);
        $psrLogger->expects(self::any())->method('error');

        $dto = $this->createMock(CslEventsSubscriberDTO::class);
        $dto->method('getCslLogger')->willReturn(new \CSL\Module\LoggerBundle\CslLogger\CslLogger($psrLogger));

        return new CslErrorSubscriber($dto);
    }
}
