<?php

declare(strict_types=1);

namespace CSL\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @template T of object
 * @extends EntityRepository<T>
 */
abstract class CslAbstractRepository extends EntityRepository
{
    protected EntityManagerInterface $entityManager;

    /**
     * @param class-string<T> $entityClass
     */
    public function __construct(EntityManagerInterface $entityManager, string $entityClass)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata($entityClass));

        $this->entityManager = $entityManager;
    }
}
