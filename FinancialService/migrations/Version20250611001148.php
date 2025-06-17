<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611001148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create expenses table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('expenses');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('wallet_id', 'bigint');
        $table->addColumn('category_id', 'bigint');
        $table->addColumn('payment_method_id', 'bigint');
        $table->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2]);
        $table->addColumn('location', 'text');
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('date', 'date');
        $table->addColumn('month', 'integer', ['notnull' => true]);
        $table->addColumn('year', 'integer', ['notnull' => true]);
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['month'], 'idx_entries_month');
        $table->addIndex(['year'], 'idx_entries_year');
        $table->addForeignKeyConstraint('wallets', ['wallet_id'], ['id']);
        $table->addForeignKeyConstraint('expense_categories', ['category_id'], ['id']);
        $table->addForeignKeyConstraint('payment_methods', ['payment_method_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('expenses');
    }
}
