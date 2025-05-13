<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250513133144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Preset category table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `categories` (`id`, `name`) VALUES
        (1, 'Salario'),
        (2, 'Transporte'),
        (3, 'Entreterimento'),
        (4, 'Saúde'),
        (5, 'Lazer'),
        (6, 'Compras'),
        (7, 'Viagens'),
        (8, 'Educação'),
        (9, 'Comida');");

    }

    public function down(Schema $schema): void
    {
        $this->addSql("TRUNCATE TABLE `categories`;");
    }
}
