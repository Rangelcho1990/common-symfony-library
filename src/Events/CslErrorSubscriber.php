<?php

declare(strict_types=1);

namespace CSL\Events;

use CSL\Module\LoggerBundle\DTO\CslLogRequestDataDTO;
use CSL\Module\LoggerBundle\DTO\CslLogTraceDataDTO;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CslErrorSubscriber extends CslAbstractSubscriber
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $cslLogRequestDataDTO = new CslLogRequestDataDTO();
        $cslLogRequestDataDTO->prepareLogRequestData(
            $event->getRequest()->request->all(),
            $event->getRequest()->getRequestUri(),
            $event->getRequest()->getMethod(),
            $this->requestUid,
            $event->getRequest()->getClientIps(),
        );

        $cslLogTraceDataDTO = new CslLogTraceDataDTO();
        $cslLogTraceDataDTO->prepareLogTraceData(
            'Error',
            null,
            null,
            $event->getThrowable()->getMessage(),
            null,
            null,
            $event->getThrowable()->getTrace(),
            $event->getThrowable()->getCode()
        );

        $this->cslLogger->logError($cslLogRequestDataDTO, $cslLogTraceDataDTO);
        unset($cslLogRequestDataDTO, $cslLogTraceDataDTO);

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
