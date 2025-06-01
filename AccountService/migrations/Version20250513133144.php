<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250513133144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration to create monthly_budgets table for budget planning per category';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('monthly_budgets');
        $table->addColumn('id', 'bigint', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('wallet_id', 'bigint', ['unsigned' => true, 'notnull' => true]);
        $table->addColumn('category_id', 'bigint', ['unsigned' => true, 'notnull' => true]);
        $table->addColumn('budgeted_amount', 'decimal', ['precision' => 15, 'scale' => 2, 'notnull' => true]);
        $table->addColumn('month', 'smallint', ['unsigned' => true, 'notnull' => true, 'comment' => '1-12']);
        $table->addColumn('year', 'smallint', ['unsigned' => true, 'notnull' => true]);
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);

        // Add check constraint for month
        $table->addOption('check_constraints', [
            'chk_month' => '(month >= 1 AND month <= 12)'
        ]);

        // Foreign keys
        $table->addForeignKeyConstraint('categories', ['category_id'], ['id'], ['onDelete' => 'RESTRICT']);

        // Indexes
        $table->addIndex(['wallet_id'], 'idx_wallet_id');
        $table->addIndex(['category_id'], 'idx_category_id');
        $table->addIndex(['year', 'month'], 'idx_period');

        // Unique constraint
        $table->addUniqueIndex(['wallet_id', 'category_id', 'year', 'month'], 'uk_wallet_category_period');

        // Add comment to table
        $table->addOption('comment', 'Monthly budget per category');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('monthly_budgets');
    }
}
