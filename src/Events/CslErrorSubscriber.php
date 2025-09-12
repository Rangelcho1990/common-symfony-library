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
        $responseData = [
            'error' => [
                'resource' => $event->getRequest()->getRequestUri(),
                'method' => $event->getRequest()->getMethod(),
                'message' => $event->getThrowable()->getMessage(),
                'code' => $event->getThrowable()->getCode(),
            ],
        ];

        $event->setResponse(
            new Response(
                json_encode($responseData),
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
