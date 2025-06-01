<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250601184615 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create icons table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('icons');
        $table->addColumn('id', 'bigint', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 100, 'notnull' => true]);
        $table->addColumn('color', 'string', ['length' => 10, 'notnull' => false, 'comment' => 'Hexadecimal color (#FFFFFF)']);
        $table->addColumn('icon_file', 'string', ['length' => 150, 'notnull' => false]);
        $table->addColumn('status', 'boolean', ['default' => true]);
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        // Remove all inserted categories
        $this->addSql('DROP TABLE IF EXISTS icons');
    }
}
