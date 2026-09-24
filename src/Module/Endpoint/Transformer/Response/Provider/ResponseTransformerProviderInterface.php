<?php

declare(strict_types=1);

namespace CSL\Module\Endpoint\Transformer\Response\Provider;

use CSL\Module\Endpoint\Transformer\Response\ResponseTransformerInterface;

interface ResponseTransformerProviderInterface
{
    /** @param class-string $transformerClass */
    public function get(string $transformerClass): ResponseTransformerInterface;
}
