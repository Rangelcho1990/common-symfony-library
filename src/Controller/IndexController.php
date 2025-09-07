<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use RedisService\Core\Container\RedisContainer;

class IndexController extends AbstractController
{
    private RedisContainer $redisContainer;

    public function __construct(RedisContainer $redis)
    {
        $this->redisContainer = $redis;
    }

    #[Route('/list', name: 'list', methods: 'GET')]
    public function list()
    {
        return $this->json([
            'Redis connection' => $this->redisContainer->isConnected(),
        ]);
    }
}
