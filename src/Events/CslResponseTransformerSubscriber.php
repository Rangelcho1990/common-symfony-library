<?php

declare(strict_types=1);

namespace CSL\Events;

use CSL\Endpoints\Examples\ExampleList\Controller\Transformer\Response\ExampleTransformer;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CslResponseTransformerSubscriber extends CslAbstractSubscriber
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        // TODO: get constraint dynamically.
        $constraint = new ExampleTransformer();
        $transformedContent = $constraint->transformContent();

        $event
            ->getResponse()
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
