<?php

declare(strict_types=1);

namespace CSL\Module\Endpoint\Transformer\Response\Provider;

use CSL\Module\Endpoint\Transformer\Response\ResponseTransformerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

final class ResponseTransformerProvider implements ResponseTransformerProviderInterface
{
    public function __construct(
        #[AutowireLocator(ResponseTransformerInterface::class)]
        private readonly ContainerInterface $transformers,
    ) {
    }

    public function get(string $transformerClass): ResponseTransformerInterface
    {
        if (!$this->transformers->has($transformerClass)) {
            throw new \LogicException(sprintf('Response transformer "%s" must be registered as an autoconfigured service implementing %s.', $transformerClass, ResponseTransformerInterface::class));
        }

        $transformer = $this->transformers->get($transformerClass);
        if (!$transformer instanceof ResponseTransformerInterface) {
            throw new \LogicException(sprintf('Response transformer service "%s" must implement %s.', $transformerClass, ResponseTransformerInterface::class));
        }

        return $transformer;
    }
}
