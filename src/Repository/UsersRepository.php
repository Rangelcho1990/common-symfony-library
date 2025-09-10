<?php

declare(strict_types=1);

namespace CSL\Repository;

use CSL\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends CslAbstractRepository<User>
 */
final class UsersRepository extends CslAbstractRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, User::class);
    }
}
