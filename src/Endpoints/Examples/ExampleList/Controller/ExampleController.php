<?php

declare(strict_types=1);

namespace CSL\Endpoints\Examples\ExampleList\Controller;

use CSL\Controller\CslAbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ExampleController extends CslAbstractController
{
    #[Route('/users', name: 'users_list', methods: 'GET')]
    public function list(): JsonResponse
    {
        return $this->json([
            'Redis connection' => $this->redisContainer->isConnected(),
        ]);
    }
}
