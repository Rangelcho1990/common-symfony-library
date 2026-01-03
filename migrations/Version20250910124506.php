<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250910124506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Users table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
           CREATE TABLE `examples` (
              `id` int(10) UNSIGNED AUTO_INCREMENT NOT NULL,
              `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
            PRIMARY KEY(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users');
    }
}
