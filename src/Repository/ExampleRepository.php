<?php

declare(strict_types=1);

namespace CSL\Repository;

use CSL\Entity\Example;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends CslAbstractRepository<Example>
 */
final class ExampleRepository extends CslAbstractRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Example::class);
    }
}
