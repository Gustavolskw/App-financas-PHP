<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250408215700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Generating accounts table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE `accounts` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `userId` bigint NOT NULL,
  `userEmail` varchar(255) NOT NULL,
  `name` varchar(80) NOT NULL,
  `description` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)");

    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('accounts');

    }
}
