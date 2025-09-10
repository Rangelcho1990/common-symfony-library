<?php

declare(strict_types=1);

namespace CSL\Controller;

use RedisService\Core\Container\RedisContainer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends CslAbstractController
{
    private RedisContainer $redisContainer;

    public function __construct(RedisContainer $redis)
    {
        $this->redisContainer = $redis;
    }

    #[Route('/list', name: 'list', methods: 'GET')]
    public function list(): JsonResponse
    {
        return $this->json([
            'Redis connection' => $this->redisContainer->isConnected(),
        ]);
    }
}
