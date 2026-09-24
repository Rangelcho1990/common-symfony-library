<?php

declare(strict_types=1);

namespace CSL\Events;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Transforms internal response payloads before returning to the client.
 * Skips framework/docs routes so Symfony and Nelmio responses are not rewritten.
 */
class CslResponseInternalSubscriber extends CslAbstractSubscriber
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        if ($this->isDocsRequest($request)) {
            return;
        }

        if ($request->attributes->getBoolean(self::CSL_ERROR_HANDLED)) {
            return;
        }

        if ($response->getStatusCode() >= 400) {
            return;
        }

        $routeName = $request->attributes->get('_route');
        if (!is_string($routeName) || '' === $routeName) {
            return;
        }

        $transformerClass = $this->cslEventsSubscriberDTO->getResponseTransformerValidator()->validate($routeName);
        $constraint = $this->cslEventsSubscriberDTO->getResponseTransformerProvider()->get($transformerClass);

        $response
            ->setContent($constraint->transformContent())
            ->setStatusCode($constraint->getStatusCode())
            ->headers->set('Content-Type', $constraint->getContentType());
    }

    /**
     * @codeCoverageIgnore
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 100],
        ];
    }
}
