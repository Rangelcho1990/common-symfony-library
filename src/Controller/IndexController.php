<?php

namespace CSL\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use RedisService\Core\Container\RedisContainer;

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
