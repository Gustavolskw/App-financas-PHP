<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611001333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed default values for payment_methods';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO payment_methods (name) VALUES ('Dinheiro')");
        $this->addSql("INSERT INTO payment_methods (name) VALUES ('Vale Alimentação')");
        $this->addSql("INSERT INTO payment_methods (name) VALUES ('Cartão Crédito')");
        $this->addSql("INSERT INTO payment_methods (name) VALUES ('Cartão Débito')");
        $this->addSql("INSERT INTO payment_methods (name) VALUES ('PIX')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM payment_methods");
    }
}
