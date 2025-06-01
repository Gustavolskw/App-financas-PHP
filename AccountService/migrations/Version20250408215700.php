<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250408215700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration to create categories table for income and expense categories';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('categories');
        $table->addColumn('id', 'bigint', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 100, 'notnull' => true]);
        $table->addColumn('type', 'boolean', ['notnull' => true, 'comment' => 'true for income, false for expense']);
        $table->addColumn('icon_id', 'bigint', ['unsigned' => true, 'notnull' => false, 'comment' => 'Foreign key to icons table']);
        $table->addForeignKeyConstraint('icons', ['icon_id'], ['id'], ['onDelete' => 'SET NULL'], 'fk_category_icon');
        $table->addColumn('status', 'boolean', ['default' => true]);
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);

        $table->addIndex(['type'], 'idx_type');
        $table->addIndex(['status'], 'idx_active');
        $table->addIndex(['icon_id'], 'idx_icon_id');
        // Add comment to table
        $table->addOption('comment', 'Income and expense categories');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('categories');
    }
}
