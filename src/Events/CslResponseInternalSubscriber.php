<?php

declare(strict_types=1);

namespace CSL\Events;

use CSL\Endpoints\Examples\ExampleList\Controller\Transformer\Response\ExampleTransformer;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Transforms internal response payloads before returning to the client.
 * Keep this subscriber opt-in (route/flag guarded) to avoid rewriting error responses.
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

        if ($request->attributes->getBoolean('_csl_error_handled')) {
            return;
        }

        if ($response->getStatusCode() >= 400) {
            return;
        }

        // TODO: get constraint dynamically.
        $constraint = new ExampleTransformer();
        $transformedContent = $constraint->transformContent();

        $response
            ->setContent($transformedContent)
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
