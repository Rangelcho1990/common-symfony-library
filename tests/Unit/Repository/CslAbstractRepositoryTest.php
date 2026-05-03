<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Repository;

use CSL\Entity\Example;
use CSL\Repository\CslAbstractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CslAbstractRepositoryTest extends TestCase
{
    public function testConstructorUsesEntityManagerMetadataForConfiguredEntity(): void
    {
        /** @var ClassMetadata<Example> $metadata */
        $metadata = new ClassMetadata(Example::class);

        /** @var EntityManagerInterface&MockObject $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::once())
            ->method('getClassMetadata')
            ->with(Example::class)
            ->willReturn($metadata);

        $repository = new CslAbstractRepositoryStub($entityManager);

        self::assertSame(Example::class, $repository->getClassName());
        self::assertSame($entityManager, $repository->exposedEntityManager());
        self::assertSame($metadata, $repository->exposedClassMetadata());
    }
}

/**
 * @extends CslAbstractRepository<Example>
 */
final class CslAbstractRepositoryStub extends CslAbstractRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Example::class);
    }

    public function exposedEntityManager(): EntityManagerInterface
    {
        return $this->getEntityManager();
    }

    /**
     * @return ClassMetadata<Example>
     */
    public function exposedClassMetadata(): ClassMetadata
    {
        return $this->getClassMetadata();
    }
}
