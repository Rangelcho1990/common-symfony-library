<?php

declare(strict_types=1);

namespace CSL\Tests\Functional\Migrations;

use CSL\Tests\Functional\KernelTestCaseBase;
use CSL\Tests\Support\TestDatabaseGuard;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\MigrationPlan;
use Doctrine\Migrations\Metadata\MigrationPlanList;
use Doctrine\Migrations\Metadata\Storage\MetadataStorage;
use Doctrine\Migrations\MigratorConfiguration;
use Doctrine\Migrations\Version\Direction;
use Doctrine\Migrations\Version\Version;
use DoctrineMigrations\Version20250910124506;
use Psr\Log\NullLogger;

require_once dirname(__DIR__, 3).'/migrations/Version20250910124506.php';

final class ExamplesMigrationTest extends KernelTestCaseBase
{
    public function testMigrationRoundTripPreservesUnrelatedUsers(): void
    {
        $kernel = self::bootKernel();
        $connection = self::getContainer()->get(Connection::class);
        self::assertInstanceOf(Connection::class, $connection);
        TestDatabaseGuard::assertSafe($kernel->getEnvironment(), $connection);
        $schemaManager = $connection->createSchemaManager();
        self::assertFalse($schemaManager->tablesExist(['users']), 'This test needs a disposable database without a users table.');

        $users = new Table('users');
        $users->addColumn('name', Types::STRING, ['length' => 100]);
        $schemaManager->createTable($users);

        try {
            $connection->insert('users', ['name' => 'Preserve me']);
            if ($schemaManager->tablesExist(['examples'])) {
                $schemaManager->dropTable('examples');
            }

            $factory = DependencyFactory::fromConnection(
                new ExistingConfiguration(new Configuration()),
                new ExistingConnection($connection),
            );
            // Exercise the real migration executor without changing application migration history.
            $factory->setDefinition(MetadataStorage::class, fn (): MetadataStorage => $this->createStub(MetadataStorage::class));

            // Repeat to verify that rollback leaves the migration runnable again.
            for ($cycle = 0; $cycle < 2; ++$cycle) {
                foreach ([Direction::UP, Direction::DOWN] as $direction) {
                    $migration = new Version20250910124506($connection, new NullLogger());
                    $plan = new MigrationPlan(new Version($migration::class), $migration, $direction);
                    $factory->getMigrator()->migrate(new MigrationPlanList([$plan], $direction), new MigratorConfiguration());

                    if (Direction::UP === $direction) {
                        self::assertTrue($schemaManager->tablesExist(['examples']));
                        $table = $schemaManager->introspectTable('examples');
                        self::assertSame(100, $table->getColumn('name')->getLength());
                        self::assertTrue($table->getColumn('name')->getNotnull());
                        self::assertSame(['id'], $table->getPrimaryKey()?->getColumns());
                        self::assertSame('User A', $connection->fetchOne('SELECT name FROM examples WHERE id = 1'));
                        self::assertSame('User B', $connection->fetchOne('SELECT name FROM examples WHERE id = 2'));
                        self::assertCount(2, $connection->fetchFirstColumn('SELECT id FROM examples'));
                        $connection->insert('examples', ['name' => 'Example']);
                        $id = $connection->fetchOne("SELECT id FROM examples WHERE name = 'Example'");
                        self::assertIsNumeric($id);
                        self::assertSame(3, (int) $id);
                    } else {
                        self::assertFalse($schemaManager->tablesExist(['examples']));
                    }
                    self::assertSame(['Preserve me'], $connection->fetchFirstColumn('SELECT name FROM users'));
                }
            }
        } finally {
            if ($schemaManager->tablesExist(['examples'])) {
                $schemaManager->dropTable('examples');
            }
            $schemaManager->dropTable('users');
            $connection->close();
        }
    }
}
