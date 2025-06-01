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
        return 'Migration to create transactions table for financial movements';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('transactions');
        $table->addColumn('id', 'bigint', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('wallet_id', 'bigint', ['unsigned' => true, 'notnull' => true]);
        $table->addColumn('category_id', 'bigint', ['unsigned' => true, 'notnull' => true]);
        $table->addColumn('description', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('amount', 'decimal', ['precision' => 15, 'scale' => 2, 'notnull' => true]);
        $table->addColumn('is_income', 'boolean', ['notnull' => true, 'comment' => 'true for income, false for expense']);
        $table->addColumn('transaction_date', 'date', ['notnull' => true]);
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);

        // Foreign keys
        $table->addForeignKeyConstraint('wallets', ['wallet_id'], ['id'], ['onDelete' => 'RESTRICT']);
        $table->addForeignKeyConstraint('categories', ['category_id'], ['id'], ['onDelete' => 'RESTRICT']);

        // Indexes
        $table->addIndex(['wallet_id'], 'idx_wallet_id');
        $table->addIndex(['category_id'], 'idx_category_id');
        $table->addIndex(['is_income'], 'idx_is_income');
        $table->addIndex(['transaction_date'], 'idx_date');
        $table->addIndex(['wallet_id', 'transaction_date'], 'idx_wallet_date');

        // Add comment to table
        $table->addOption('comment', 'Financial transactions');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('transactions');
    }
}
