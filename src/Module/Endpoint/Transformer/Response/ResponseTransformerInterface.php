<?php

declare(strict_types=1);

namespace CSL\Module\Endpoint\Transformer\Response;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface ResponseTransformerInterface
{
    public function transformContent(): string;

    public function getStatusCode(): int;

    public function getContentType(): string;
}
