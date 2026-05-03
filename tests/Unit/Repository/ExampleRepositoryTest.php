<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Repository;

use CSL\Entity\Example;
use CSL\Repository\CslAbstractRepository;
use CSL\Repository\ExampleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ExampleRepositoryTest extends TestCase
{
    public function testConstructsRepositoryForExampleEntity(): void
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

        $repository = new ExampleRepository($entityManager);

        self::assertInstanceOf(CslAbstractRepository::class, $repository);
        self::assertInstanceOf(EntityRepository::class, $repository);
        self::assertSame(Example::class, $repository->getClassName());
    }
}
