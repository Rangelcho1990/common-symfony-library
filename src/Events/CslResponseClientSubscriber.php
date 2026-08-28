<?php

declare(strict_types=1);

namespace CSL\Events;

use CSL\Events\DTO\CslEventsSubscriberDTO;
use CSL\Module\LoggerBundle\DTO\CslLogRequestDataDTO;
use CSL\Module\LoggerBundle\DTO\CslLogTraceDataDTO;
use CSL\Service\ClientCommunicator\ClientCommunicatorInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CslResponseClientSubscriber extends CslAbstractSubscriber
{
    private ClientCommunicatorInterface $clientCommunicatorInterface;

    public function __construct(CslEventsSubscriberDTO $cslEventsSubscriberDTO, ClientCommunicatorInterface $clientCommunicatorInterface)
    {
        parent::__construct($cslEventsSubscriberDTO);

        $this->clientCommunicatorInterface = $clientCommunicatorInterface;
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($this->isDocsRequest($event->getRequest())) {
            return;
        }

        if (!$event->isMainRequest()) {
            return;
        }

        // log communication time
        $requestData = array_merge(
            $this->getRequestQueryData($event->getRequest()),
            $this->getRequestRawData($event->getRequest()),
        );

        $cslLogRequestDataDTO = new CslLogRequestDataDTO();
        $cslLogRequestDataDTO->prepareLogRequestData(
            $requestData,
            $event->getRequest()->getRequestUri(),
            $event->getRequest()->getMethod(),
            $this->getRequestUid($event),
            $event->getRequest()->getClientIps(),
        );

        $communicationTime = [];
        $clientId = $event->getRequest()->attributes->get(self::CLIENT_ID);
        if (is_string($clientId)) {
            $this->clientCommunicatorInterface->stopTimer($clientId);
            $communicationTime = $this->clientCommunicatorInterface->getCommunicationTime($clientId);
        }

        $content = $event->getResponse()->getContent();
        $content = false === $content ? null : $content;

        $cslLogTraceDataDTO = new CslLogTraceDataDTO();
        $cslLogTraceDataDTO->prepareLogTraceData(
            'Info',
            $communicationTime,
            $content,
            'TraceReqeust',
            null,
            null,
            null,
            $event->getResponse()->getStatusCode()
        );
        unset($clientId, $content);

        $this->cslLogger->getInfoEvents()->logInfo(
            $cslLogRequestDataDTO,
            $cslLogTraceDataDTO,
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 50],
        ];
    }

    private function getRequestUid(ResponseEvent $event): UuidInterface
    {
        $requestUid = $event->getRequest()->attributes->get(self::REQUEST_UID);
        if ($requestUid instanceof UuidInterface) {
            return $requestUid;
        }

        $requestUid = Uuid::uuid7();
        $event->getRequest()->attributes->set(self::REQUEST_UID, $requestUid);

        return $requestUid;
    }
}
