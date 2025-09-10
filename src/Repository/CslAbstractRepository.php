<?php

declare(strict_types=1);

namespace CSL\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class CslAbstractRepository extends EntityRepository
{
    protected  EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, string $entityClass)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata($entityClass));

        $this->entityManaget = $entityManager;
    }
}
