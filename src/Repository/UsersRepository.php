<?php

declare(strict_types=1);

namespace CSL\Repository;

use CSL\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends CslAbstractRepository<Users>
 */
final class UsersRepository extends CslAbstractRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Users::class);
    }
}
