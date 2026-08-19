<?php

declare(strict_types=1);

namespace CSL\Events;

use CSL\Events\DTO\CslEventsSubscriberDTO;
use CSL\Service\ClientCommunicator\ClientCommunicatorInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CslRequestClientSubscriber extends CslAbstractSubscriber
{
    private ClientCommunicatorInterface $clientCommunicatorInterface;

    public function __construct(CslEventsSubscriberDTO $cslEventsSubscriberDTO, ClientCommunicatorInterface $clientCommunicatorInterface)
    {
        parent::__construct($cslEventsSubscriberDTO);

        $this->clientCommunicatorInterface = $clientCommunicatorInterface;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($this->isDocsRequest($event->getRequest())) {
            return;
        }

        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        $requestUid = $request->attributes->get(self::REQUEST_UID);
        if (!$requestUid instanceof UuidInterface) {
            $requestUid = Uuid::uuid7();
            $request->attributes->set(self::REQUEST_UID, $requestUid);
        }

        $clientId = $request->attributes->get(self::CLIENT_ID);
        if (!is_string($clientId)) {
            $clientId = 'Communication_'.$requestUid->toString();
            $request->attributes->set(self::CLIENT_ID, $clientId);
        }

        $this->clientCommunicatorInterface->startTimer($clientId);
    }

    /**
     * @codeCoverageIgnore
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 300],
        ];
    }
}
