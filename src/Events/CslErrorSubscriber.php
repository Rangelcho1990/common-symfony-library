<?php

declare(strict_types=1);

namespace CSL\Events;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CslErrorSubscriber extends CslAbstractSubscriber
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $this->cslLogger->addContext([
            'messageTemplate' => 'Error',
            'resource'        => $event->getRequest()->getRequestUri(),
            'method'          => $event->getRequest()->getMethod(),
            'message'         => $event->getThrowable()->getMessage(),
            'code'            => $event->getThrowable()->getCode(),
            'stackTrace'      => $event->getThrowable()->getTrace(),
            'requestUid'      => $this->requestUid,
        ]);

        $this->cslLogger->logger->error(
            'Error',
            $this->cslLogger->getContext()
        );

        $event->setResponse(
            new Response(
                json_encode([
                    'message' => $event->getThrowable()->getMessage(),
                    'code'    => Response::HTTP_INTERNAL_SERVER_ERROR,
                ]),
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
