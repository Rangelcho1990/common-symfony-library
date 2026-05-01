<?php

declare(strict_types=1);

namespace CSL\Events;

use CSL\DTO\Events\CslEventsSubscriberDTO;
use CSL\Module\LoggerBundle\DTO\CslLogRequestDataDTO;
use CSL\Module\LoggerBundle\DTO\CslLogTraceDataDTO;
use CSL\Service\ClientCommunicator\ClientCommunicatorInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CslClientCommunicatorSubscriber extends CslAbstractSubscriber
{
    private ClientCommunicatorInterface $clientCommunicatorInterface;

    public function __construct(CslEventsSubscriberDTO $cslEventsSubscriberDTO, ClientCommunicatorInterface $clientCommunicatorInterface)
    {
        parent::__construct($cslEventsSubscriberDTO);

        $this->clientCommunicatorInterface = $clientCommunicatorInterface;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $this->clientCommunicatorInterface->startTimer($this->clientId);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $requestData = array_merge(
            $this->getRequestQueryData($event->getRequest()),
            $this->getRequestRawData($event->getRequest()),
        );

        $cslLogRequestDataDTO = new CslLogRequestDataDTO();
        $cslLogRequestDataDTO->prepareLogRequestData(
            $requestData,
            $event->getRequest()->getRequestUri(),
            $event->getRequest()->getMethod(),
            $this->requestUid,
            $event->getRequest()->getClientIps(),
        );

        $this->clientCommunicatorInterface->stopTimer($this->clientId);

        $other = [
            'communicationTime' => $this->clientCommunicatorInterface->getCommunicationTime($this->clientId),
        ];

        $content = $event->getResponse()->getContent();
        $content = false === $content ? null : $content;

        $cslLogTraceDataDTO = new CslLogTraceDataDTO();
        $cslLogTraceDataDTO->prepareLogTraceData(
            'Info',
            $other,
            $content,
            null,
            null,
            null,
            null,
            $event->getResponse()->getStatusCode()
        );

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
            KernelEvents::REQUEST => ['onKernelRequest', 300],
            KernelEvents::RESPONSE => ['onKernelResponse', 50],
        ];
    }
}
