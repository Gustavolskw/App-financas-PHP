<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611001029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create entry_types table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('entry_types');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 100]);
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('entry_types');
    }
}
