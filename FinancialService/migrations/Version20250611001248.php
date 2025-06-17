<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611001248 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create loans table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('loans');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('expense_id', 'bigint');
        $table->addColumn('person_name', 'string', ['length' => 150]);
        $table->addColumn('status', 'boolean', ['default' => false]);
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('expenses', ['expense_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('loans');
    }
}
