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
        $this->addSql("INSERT INTO `categories` (`id`, `name`, `type_category`) VALUES
        (1, 'Salario', 1),
        (2, 'Transporte', 0),
        (3, 'Entreterimento',0),
        (4, 'Saúde',0),
        (5, 'Lazer',0),
        (6, 'Comida',0),
        (7, 'Viagens',0),
        (8, 'Educação',0);");

    }

    public function down(Schema $schema): void
    {
        $this->addSql("TRUNCATE TABLE `categories`;");
    }
}
