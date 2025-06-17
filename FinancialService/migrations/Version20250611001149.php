<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611001149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create investment_types table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('investment_types');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 100]);
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('investment_types');
    }
}
