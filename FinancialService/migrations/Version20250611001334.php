<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611001334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed default values for investment_types';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO investment_types (name) VALUES ('Renda Fixa')");
        $this->addSql("INSERT INTO investment_types (name) VALUES ('Ações')");
        $this->addSql("INSERT INTO investment_types (name) VALUES ('Cripto')");
        $this->addSql("INSERT INTO investment_types (name) VALUES ('Tesouro Direto')");
        $this->addSql("INSERT INTO investment_types (name) VALUES ('Caixinha')");
        $this->addSql("INSERT INTO investment_types (name) VALUES ('Fundo Imobiliário')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM investment_types");
    }
}
