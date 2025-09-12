<?php

declare(strict_types=1);

namespace CSL\Controller;

use RedisService\Core\Container\RedisContainer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CslAbstractController extends AbstractController
{
    protected RedisContainer $redisContainer;

    public function __construct(RedisContainer $redis)
    {
        $this->redisContainer = $redis;
    }
}
