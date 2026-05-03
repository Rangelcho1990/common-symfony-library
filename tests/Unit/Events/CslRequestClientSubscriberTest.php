<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Events;

use CSL\Events\CslRequestClientSubscriber;
use CSL\Events\DTO\CslEventsSubscriberDTO;
use CSL\Service\ClientCommunicator\ClientCommunicatorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class CslRequestClientSubscriberTest extends TestCase
{
    public function testSameRequestGetsIdenticalRequestUidAcrossRepeatedRequestHandlingCalls(): void
    {
        $communicator = $this->createMock(ClientCommunicatorInterface::class);
        $communicator->expects(self::exactly(2))->method('startTimer');

        $subscriber = $this->createSubscriber($communicator);
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();

        $event1 = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
        $subscriber->onKernelRequest($event1);
        $uid1 = $request->attributes->get('requestUid');
        self::assertNotNull($uid1);

        $event2 = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
        $subscriber->onKernelRequest($event2);
        $uid2 = $request->attributes->get('requestUid');
        self::assertNotNull($uid2);

        self::assertSame($uid1, $uid2);
    }

    public function testSubRequestsDoNotSetClientIdOrRequestUidAndDoNotStartTimer(): void
    {
        $communicator = $this->createMock(ClientCommunicatorInterface::class);
        $communicator->expects(self::never())->method('startTimer');

        $subscriber = $this->createSubscriber($communicator);
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();

        $event = new RequestEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);
        $subscriber->onKernelRequest($event);

        self::assertFalse($request->attributes->has('requestUid'));
        self::assertFalse($request->attributes->has('clientId'));
    }

    public function testMainRequestSetsClientIdAndRequestUidAndStartsTimer(): void
    {
        $communicator = $this->createMock(ClientCommunicatorInterface::class);
        $communicator->expects(self::once())->method('startTimer')->with(self::isString());

        $subscriber = $this->createSubscriber($communicator);
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();

        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
        $subscriber->onKernelRequest($event);

        self::assertTrue($request->attributes->has('requestUid'));
        self::assertTrue($request->attributes->has('clientId'));
        self::assertIsString($request->attributes->get('clientId'));
    }

    public function testMainRequestUsesExistingClientIdAndRequestUidWhenPresent(): void
    {
        $communicator = $this->createMock(ClientCommunicatorInterface::class);
        $communicator->expects(self::once())->method('startTimer')->with('ExistingClientId');

        $subscriber = $this->createSubscriber($communicator);
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = new Request();
        $existingUid = Uuid::uuid7();

        $request->attributes->set('requestUid', $existingUid);
        $request->attributes->set('clientId', 'ExistingClientId');

        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
        $subscriber->onKernelRequest($event);

        self::assertSame($existingUid, $request->attributes->get('requestUid'));
        self::assertSame('ExistingClientId', $request->attributes->get('clientId'));
    }

    private function createSubscriber(ClientCommunicatorInterface $communicator): CslRequestClientSubscriber
    {
        $psrLogger = $this->createStub(LoggerInterface::class);

        $dto = $this->createStub(CslEventsSubscriberDTO::class);
        $dto->method('getCslLogger')->willReturn(new \CSL\Module\LoggerBundle\CslLogger\CslLogger($psrLogger));

        return new CslRequestClientSubscriber($dto, $communicator);
    }
}
