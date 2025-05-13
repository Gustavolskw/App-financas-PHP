<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250513115148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration to create category table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('categories');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 45, 'notnull' => true]);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name'], 'uniq_category_name');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('categories');
    }
}
