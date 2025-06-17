<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611001030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create entries table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('entries');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('wallet_id', 'bigint');
        $table->addColumn('entry_type_id', 'bigint');
        $table->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('date', 'date');
        $table->addColumn('month', 'integer', ['notnull' => true]);
        $table->addColumn('year', 'integer', ['notnull' => true]);
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['month'], 'idx_entries_month');
        $table->addIndex(['year'], 'idx_entries_year');
        $table->addForeignKeyConstraint('wallets', ['wallet_id'], ['id']);
        $table->addForeignKeyConstraint('entry_types', ['entry_type_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('entries');
    }
}
