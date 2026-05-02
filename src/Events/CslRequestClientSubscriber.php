<?php

declare(strict_types=1);

namespace CSL\Events;

use CSL\DTO\Events\CslEventsSubscriberDTO;
use CSL\Service\ClientCommunicator\ClientCommunicatorInterface;
use Ramsey\Uuid\Uuid;
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
        if (!$event->isMainRequest()) {
            return;
        }

        $requestUid = Uuid::uuid1();
        $clientId = 'Communication_'.$requestUid->toString();

        $event->getRequest()->attributes->set('requestUid', $requestUid);
        $event->getRequest()->attributes->set('clientId', $clientId);

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
