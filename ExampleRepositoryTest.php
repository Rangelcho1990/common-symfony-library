<?php

declare(strict_types=1);

namespace CSL\Tests\Functional\Repository;

use CSL\Entity\Example;
use CSL\Repository\ExampleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ExampleRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ExampleRepository $exampleRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $em */
        $em                  = static::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager = $em;

        /** @var ExampleRepository $exampleRepo */
        $exampleRepo             = $this->entityManager->getRepository(Example::class);
        $this->exampleRepository = $exampleRepo;
        unset($em, $exampleRepo);

        $schemaTool = new SchemaTool($this->entityManager);
        $metadata   = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function testFindAllUsers(): void
    {
        $user1 = (new Example())->setName('User A');
        $user2 = (new Example())->setName('User B');

        $this->entityManager->persist($user1);
        $this->entityManager->persist($user2);
        $this->entityManager->flush();

        $result = $this->exampleRepository->findAll();

        $this->assertCount(2, $result);
        $this->assertSame('User A', $result[0]->getName());
        $this->assertSame('User B', $result[1]->getName());
    }
}
