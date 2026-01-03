<?php

declare(strict_types=1);

namespace CSL\Endpoints\Examples\ExampleList\Controller;

use CSL\Controller\CslAbstractController;
use RedisService\Core\Container\RedisContainer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ExampleController extends CslAbstractController
{
    #[Route('/example', name: 'example', methods: 'GET')]
    public function example(RedisContainer $redisContainer): JsonResponse
    {
        return $this->json([
            'Redis connection' => $redisContainer->isConnected(),
        ]);
    }
}
