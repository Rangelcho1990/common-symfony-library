<?php

declare(strict_types=1);

namespace CSL\Tests\Functional\Repository;

use CSL\Entity\Example;
use CSL\Repository\ExampleRepository;
use CSL\Tests\Functional\KernelTestCaseBase;
use CSL\Tests\Support\TestDatabaseGuard;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\Test;

final class ExampleRepositoryTest extends KernelTestCaseBase
{
    private static bool $schemaCreated = false;
    private EntityManagerInterface $entityManager;
    private ExampleRepository $exampleRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager = $em;
        TestDatabaseGuard::assertSafe($kernel->getEnvironment(), $em->getConnection());

        /** @var ExampleRepository $exampleRepo */
        $exampleRepo = $this->entityManager->getRepository(Example::class);
        $this->exampleRepository = $exampleRepo;
        unset($em, $exampleRepo);

        if (!self::$schemaCreated) {
            $schemaTool = new SchemaTool($this->entityManager);
            $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
            self::$schemaCreated = true;
        }
        $this->entityManager->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        try {
            if (isset($this->entityManager)) {
                $connection = $this->entityManager->getConnection();
                if ($connection->isTransactionActive()) {
                    $connection->rollBack();
                }
                $this->entityManager->close();
                $connection->close();
            }
        } finally {
            parent::tearDown();
        }
    }

    #[Test]
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

    #[Test]
    public function testPreviousTestDataWasRolledBack(): void
    {
        $this->assertSame([], $this->exampleRepository->findAll());
    }
}
