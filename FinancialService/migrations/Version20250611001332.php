<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611001332 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed default values for expense_categories';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Alimentação')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Transporte')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Investimento')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Empréstimo')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Educação')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Lazer')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Saúde')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Moradia')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Serviços')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Outros')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Igreja')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Fatura Cartão')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Fatura Celular')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Mercado')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Itens de Higiene')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Conta de Luz')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Conta de Água')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Conta de Internet')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Compras na Internet')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Gasolina')");
        $this->addSql("INSERT INTO expense_categories (name) VALUES ('Presentes')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM expense_categories");
    }
}
