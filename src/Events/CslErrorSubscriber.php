<?php

declare(strict_types=1);

namespace CSL\Events;

use CSL\Module\LoggerBundle\DTO\CslLogDataDTO;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CslErrorSubscriber extends CslAbstractSubscriber
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $cslLogDataDTO = new CslLogDataDTO();
        $cslLogDataDTO->setMessageTemplate('Error');
        $cslLogDataDTO->setRequestUid($this->requestUid);
        $cslLogDataDTO->setResource($event->getRequest()->getRequestUri());
        $cslLogDataDTO->setMethod($event->getRequest()->getMethod());
        $cslLogDataDTO->setMessage($event->getThrowable()->getMessage());
        $cslLogDataDTO->setCode($event->getThrowable()->getCode());
        $cslLogDataDTO->setStackTrace($event->getThrowable()->getTrace());

        $this->cslLogger->logger->error('Error', $cslLogDataDTO->getLogData());

        $responseData = json_encode([
            'message' => $event->getThrowable()->getMessage(),
            'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
        ]);

        $event->setResponse(
            new Response(
                false === $responseData ? null : $responseData,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['content-type' => 'application/json'],
            )
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException'],
        ];
    }
}
