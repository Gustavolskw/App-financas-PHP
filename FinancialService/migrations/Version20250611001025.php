<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611001025 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create wallets table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('wallets');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('user_id', 'bigint', ['notnull' => true]);
        $table->addColumn('user_email', 'string', ['length' => 150, 'notnull' => true]);
        $table->addColumn('name', 'string', ['length' => 100]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('status', 'boolean', ['default' => true]);
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['user_id'], 'uniq_user_id');
        $table->addIndex(['user_email'], 'idx_user_email');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('wallets');
    }
}
