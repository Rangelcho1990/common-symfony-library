<?php

declare(strict_types=1);

namespace CSL\Endpoints\Examples\ExampleList\Controller;

use CSL\Controller\CslAbstractController;
use OpenApi\Attributes as OA;
use RedisService\Core\Container\RedisContainer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ExampleController extends CslAbstractController
{
    #[OA\Response(
        response: 200,
        description: 'Example response',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'success', type: 'boolean'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'name', type: 'string'),
                    ],
                ),
            ],
        ),
    )]
    #[OA\Tag(name: 'Examples')]
    #[Route('/example', name: 'example', methods: 'GET')]
    public function example(RedisContainer $redisContainer): JsonResponse
    {
        return $this->json([
            'Redis connection' => $redisContainer->isConnected(),
        ]);
    }
}
