<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611001031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create expense_categories table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('expense_categories');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 100]);
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('expense_categories');
    }
}
