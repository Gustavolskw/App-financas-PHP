<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250601184622 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration to create wallets table - single wallet per user with dual validation';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('wallets');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('user_id', 'bigint', ['notnull' => true, 'comment' => 'User ID from auth microservice (unique per user)']);
        $table->addColumn('user_email', 'string', ['length' => 255, 'notnull' => true, 'comment' => 'User email for dual validation']);
        $table->addColumn('status', 'boolean', ['default' => true, 'comment' => 'Active status of the wallet']);
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);

        // Unique constraints
        $table->addUniqueIndex(['user_id'], 'uniq_user_id');
        $table->addUniqueIndex(['user_email'], 'uniq_user_email');
        $table->addUniqueIndex(['user_id', 'user_email'], 'uk_user_validation');

        // Regular indexes
        $table->addIndex(['user_id'], 'idx_user_id');
        $table->addIndex(['user_email'], 'idx_user_email');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('wallets');
    }
}
