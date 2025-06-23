<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611001150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create investments table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('investments');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('expense_id', 'bigint');
        $table->addColumn('investment_type_id', 'bigint');
        $table->addColumn('status', 'boolean', ['default' => true]);
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('expenses', ['expense_id'], ['id']);
        $table->addForeignKeyConstraint('investment_types', ['investment_type_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('investments');
    }
}
