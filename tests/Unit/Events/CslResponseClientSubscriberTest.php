<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Events;

use CSL\Events\CslResponseClientSubscriber;
use CSL\Events\DTO\CslEventsSubscriberDTO;
use CSL\Service\ClientCommunicator\ClientCommunicatorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class CslResponseClientSubscriberTest extends TestCase
{
    public function testSameRequestKeepsIdenticalRequestUidAcrossRepeatedResponseHandlingCalls(): void
    {
        $communicator = $this->createMock(ClientCommunicatorInterface::class);
        $communicator->expects(self::exactly(2))->method('stopTimer');
        $communicator->expects(self::exactly(2))->method('getCommunicationTime')->willReturn([]);

        $subscriber = $this->createSubscriber($communicator);
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();
        $request->attributes->set('clientId', 'Communication_x');
        $response = new Response('ok', 200);

        $event1 = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $subscriber->onKernelResponse($event1);
        $uid1 = $request->attributes->get('requestUid');
        self::assertNotNull($uid1);

        $event2 = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $subscriber->onKernelResponse($event2);
        $uid2 = $request->attributes->get('requestUid');
        self::assertNotNull($uid2);

        self::assertSame($uid1, $uid2);
    }

    public function testSubRequestsDoNotStopTimerOrLog(): void
    {
        $communicator = $this->createMock(ClientCommunicatorInterface::class);
        $communicator->expects(self::never())->method('stopTimer');
        $communicator->expects(self::never())->method('getCommunicationTime');

        $psrLogger = $this->createMock(LoggerInterface::class);
        $psrLogger->expects(self::never())->method('info');

        $subscriber = $this->createSubscriber($communicator, $psrLogger);
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();
        $request->attributes->set('clientId', 'Communication_x');
        $response = new Response('ok', 200);

        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST, $response);
        $subscriber->onKernelResponse($event);
    }

    public function testMainRequestStopsTimerAndDoesNotModifyResponseContent(): void
    {
        $communicator = $this->createMock(ClientCommunicatorInterface::class);
        $communicator->expects(self::once())->method('stopTimer')->with('Communication_x');
        $communicator->expects(self::once())->method('getCommunicationTime')->with('Communication_x')->willReturn([]);

        $psrLogger = $this->createMock(LoggerInterface::class);
        $psrLogger
            ->expects(self::once())
            ->method('info')
            ->with('Info', self::isArray());

        $subscriber = $this->createSubscriber($communicator, $psrLogger);
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();
        $request->attributes->set('clientId', 'Communication_x');
        $response = new Response('original', 200, ['Content-Type' => 'text/plain']);

        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $subscriber->onKernelResponse($event);

        self::assertSame('original', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('text/plain', $response->headers->get('Content-Type'));
    }

    public function testMainRequestWithoutClientIdDoesNotStopTimerButStillLogsAndSetsRequestUid(): void
    {
        $communicator = $this->createMock(ClientCommunicatorInterface::class);
        $communicator->expects(self::never())->method('stopTimer');
        $communicator->expects(self::never())->method('getCommunicationTime');

        $psrLogger = $this->createMock(LoggerInterface::class);
        $psrLogger
            ->expects(self::once())
            ->method('info')
            ->with('Info', self::isArray());

        $subscriber = $this->createSubscriber($communicator, $psrLogger);
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();
        $response = new Response('original', 200);

        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
        $subscriber->onKernelResponse($event);

        self::assertTrue($request->attributes->has('requestUid'));
        self::assertSame('original', $response->getContent());
    }

    private function createSubscriber(
        ClientCommunicatorInterface $communicator,
        ?LoggerInterface $psrLogger = null,
    ): CslResponseClientSubscriber {
        $psrLogger ??= $this->createStub(LoggerInterface::class);

        $dto = $this->createStub(CslEventsSubscriberDTO::class);
        $dto->method('getCslLogger')->willReturn(new \CSL\Module\LoggerBundle\CslLogger\CslLogger($psrLogger));

        return new CslResponseClientSubscriber($dto, $communicator);
    }
}
