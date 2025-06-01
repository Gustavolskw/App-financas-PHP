<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250601184618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration to create icons data';
    }
    public function up(Schema $schema): void
    {
        $mainIcons = [
            ['name' => 'money', 'color'=> '#4CAF50', 'icon_file' => 'money.svg', 'status' => true],
            ['name' => 'expense', 'color' => '#F44336', 'icon_file' => 'expense.svg', 'status' => true],
            ['name' => 'income', 'color' => '#2196F3', 'icon_file' => 'income.svg', 'status' => true],
            ['name' => 'savings', 'color' => '#FF9800', 'icon_file' => 'savings.svg', 'status' => true],
            ['name' => 'budget', 'color' => '#9C27B0', 'icon_file' => 'budget.svg', 'status' => true],
        ];
        foreach ($mainIcons as $mainIcon) {
            $this->addSql(
                'INSERT INTO icons (name, color, icon_file, status) VALUES (?, ?, ?, ?)',
                [
                    $mainIcon['name'],
                    $mainIcon['color'],
                    $mainIcon['icon_file'],
                    $mainIcon['status']
                ]
            );
        }
    }
    public function down(Schema $schema): void
    {
        $this->addSql("TRUNCATE TABLE icons");
    }
}
