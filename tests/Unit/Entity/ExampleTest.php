<?php

declare(strict_types=1);

namespace CSL\Tests\Unit\Entity;

use CSL\Entity\Example;
use CSL\Repository\ExampleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    public function testAccessorsAreFluent(): void
    {
        $example = new Example();

        self::assertSame($example, $example->setId(123));
        self::assertSame($example, $example->setName('Example name'));
        self::assertSame(123, $example->getId());
        self::assertSame('Example name', $example->getName());
    }

    public function testDoctrineMappingAttributes(): void
    {
        $reflection = new \ReflectionClass(Example::class);

        $tableAttributes = $reflection->getAttributes(Table::class);
        self::assertCount(1, $tableAttributes);

        /** @var Table $table */
        $table = $tableAttributes[0]->newInstance();
        self::assertSame('examples', $table->name);

        $entityAttributes = $reflection->getAttributes(Entity::class);
        self::assertCount(1, $entityAttributes);

        /** @var Entity<Example> $entity */
        $entity = $entityAttributes[0]->newInstance();
        self::assertSame(ExampleRepository::class, $entity->repositoryClass);

        $idProperty = $reflection->getProperty('id');
        self::assertCount(1, $idProperty->getAttributes(Id::class));
        self::assertCount(1, $idProperty->getAttributes(GeneratedValue::class));

        $idColumnAttributes = $idProperty->getAttributes(Column::class);
        self::assertCount(1, $idColumnAttributes);

        /** @var Column $idColumn */
        $idColumn = $idColumnAttributes[0]->newInstance();
        self::assertSame('integer', $idColumn->type);

        $nameProperty = $reflection->getProperty('name');
        $nameColumnAttributes = $nameProperty->getAttributes(Column::class);
        self::assertCount(1, $nameColumnAttributes);

        /** @var Column $nameColumn */
        $nameColumn = $nameColumnAttributes[0]->newInstance();
        self::assertSame('name', $nameColumn->name);
        self::assertSame(Types::STRING, $nameColumn->type);
        self::assertSame(100, $nameColumn->length);
    }
}
