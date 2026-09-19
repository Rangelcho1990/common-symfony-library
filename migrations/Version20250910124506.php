<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250910124506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create examples table and seed User A and User B.';
    }

    public function up(Schema $schema): void
    {
        $table = new Table('examples');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('name', Types::STRING, ['length' => 100]);
        $table->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setColumnNames(UnqualifiedName::unquoted('id'))
                ->create(),
        );

        // Queue table creation before inserts, including when generating migration SQL.
        foreach ($this->connection->getDatabasePlatform()->getCreateTableSQL($table) as $sql) {
            $this->addSql($sql);
        }

        // A new table assigns IDs 1 and 2 and advances the identity on both supported platforms.
        $this->addSql("INSERT INTO examples (name) VALUES ('User A')");
        $this->addSql("INSERT INTO examples (name) VALUES ('User B')");
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('examples');
    }
}
