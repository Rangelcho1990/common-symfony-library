<?php

declare(strict_types=1);

namespace CSL\Events\DTO;

use CSL\Module\Endpoint\Transformer\Response\Provider\ResponseTransformerProviderInterface;
use CSL\Module\Endpoint\Transformer\Response\Validation\ResponseTransformerValidatorInterface;
use CSL\Module\LoggerBundle\CslLogger\CslLoggerInterface;

class CslEventsSubscriberDTO
{
    public function __construct(
        private readonly ResponseTransformerProviderInterface $responseTransformerProvider,
        private readonly ResponseTransformerValidatorInterface $responseTransformerValidator,
        private readonly CslLoggerInterface $cslLogger,
    ) {
    }

    public function getResponseTransformerProvider(): ResponseTransformerProviderInterface
    {
        return $this->responseTransformerProvider;
    }

    public function getResponseTransformerValidator(): ResponseTransformerValidatorInterface
    {
        return $this->responseTransformerValidator;
    }

    public function getCslLogger(): CslLoggerInterface
    {
        return $this->cslLogger;
    }
}
