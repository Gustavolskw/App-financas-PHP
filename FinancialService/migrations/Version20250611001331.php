<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611001331 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed default values for entry_types';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO entry_types (name) VALUES ('Salário')");
        $this->addSql("INSERT INTO entry_types (name) VALUES ('Vale Alimentação')");
        $this->addSql("INSERT INTO entry_types (name) VALUES ('Saldo Anterior')");
        $this->addSql("INSERT INTO entry_types (name) VALUES ('Resgate Investimento')");
        $this->addSql("INSERT INTO entry_types (name) VALUES ('Devolução Empréstimo')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM entry_types");
    }
}
