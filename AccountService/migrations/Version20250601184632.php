<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250601184632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration to create monthly_budgets table for budget planning per category';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('monthly_budgets');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('wallet_id', 'bigint', ['notnull' => true]);
        $table->addColumn('category_id', 'bigint', ['notnull' => true]);
        $table->addColumn('budgeted_amount', 'decimal', ['precision' => 15, 'scale' => 2, 'notnull' => true]);
        $table->addColumn('month', 'smallint', ['notnull' => true, 'comment' => '1-12']);
        $table->addColumn('year', 'smallint', ['notnull' => true]);
        $table->addColumn('confirmed', 'boolean', ['default' => false, 'notnull' => true]);
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('categories', ['category_id'], ['id'], ['onDelete' => 'CASCADE']);
        $table->addForeignKeyConstraint('wallets', ['wallet_id'], ['id'], ['onDelete' => 'CASCADE']);
        $table->addIndex(['wallet_id'], 'idx_wallet_id');
        $table->addIndex(['category_id'], 'idx_category_id');
        $table->addIndex(['year', 'month'], 'idx_period');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('monthly_budgets');
    }
}
