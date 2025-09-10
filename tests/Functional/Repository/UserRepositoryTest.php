<?php

declare(strict_types=1);

namespace CSL\Tests\Functional\Repository;

use CSL\Entity\User;
use CSL\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UserRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private UsersRepository $usersRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->usersRepository = $this->entityManager->getRepository(User::class);

        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function testFindActiveUsers(): void
    {
        $user1 = (new User())
            ->setName('User A');

        $user2 = (new User())
            ->setName('User B');

        $this->entityManager->persist($user1);
        $this->entityManager->persist($user2);
        $this->entityManager->flush();

        $result = $this->usersRepository->findAll();

        $this->assertCount(2, $result);
        $this->assertSame('User A', $result[0]->getName());
        $this->assertSame('User B', $result[1]->getName());
    }
}
