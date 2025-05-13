<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250513132337 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration to create transactions table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('transactions');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('account_id', 'bigint');
        $table->addColumn('type', 'boolean');
        $table->addColumn('category_id', 'bigint');
        $table->addColumn('amount', 'bigint');
        $table->addColumn('date', 'datetime')->setDefault('CURRENT_TIMESTAMP');
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('accounts', ['account_id'], ['id'], ['onDelete' => 'CASCADE']);
        $table->addForeignKeyConstraint('categories', ['category_id'], ['id'], ['onDelete' => 'CASCADE']);
        $table->addIndex(['account_id'], 'idx_account_id');
        $table->addIndex(['category_id'], 'idx_category_id');
        $table->addIndex(['type'], 'idx_type');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('transactions');
    }
}
