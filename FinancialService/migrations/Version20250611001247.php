<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611001247 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create investment_redemptions table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('investment_redemptions');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('investment_id', 'bigint');
        $table->addColumn('entry_id', 'bigint');
        $table->addColumn('date', 'date');
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('investments', ['investment_id'], ['id']);
        $table->addForeignKeyConstraint('entries', ['entry_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('investment_redemptions');
    }
}
