<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250601184805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Inserção inicial reduzida de categorias de receitas e despesas com referência a ícones';
    }

    public function up(Schema $schema): void
    {
        // Categorias de receita (type = true)
        $incomeCategories = [
            ['name' => 'Salário',            'type' => 1, 'icon_id' => 3, 'status' => true], // receita
            ['name' => 'Freelance',          'type' => 1, 'icon_id' => 6, 'status' => true], // compras
            ['name' => 'Investimentos',      'type' => 1, 'icon_id' => 11, 'status' => true], // educação
            ['name' => 'Outros Rendimentos', 'type' => 1, 'icon_id' => 1, 'status' => true], // dinheiro
        ];

        // Categorias de despesa (type = false)
        $expenseCategories = [
            ['name' => 'Aluguel',          'type' => 0, 'icon_id' => 5, 'status' => true],  // orçamento
            ['name' => 'Supermercado',     'type' => 0, 'icon_id' => 8, 'status' => true],  // alimentação
            ['name' => 'Transporte',       'type' => 0, 'icon_id' => 10, 'status' => true], // transporte
            ['name' => 'Saúde',            'type' => 0, 'icon_id' => 9, 'status' => true],  // saúde
            ['name' => 'Educação',         'type' => 0, 'icon_id' => 11, 'status' => true], // educação
            ['name' => 'Lazer',            'type' => 0, 'icon_id' => 7, 'status' => true],  // viagem
            ['name' => 'Outras Despesas',  'type' => 0, 'icon_id' => 2, 'status' => true],  // despesa
        ];

        foreach ($incomeCategories as $category) {
            $this->addSql(
                'INSERT INTO categories (name, type, icon_id, status) VALUES (?, ?, ?, ?)',
                [$category['name'], $category['type'], $category['icon_id'], $category['status']]
            );
        }

        foreach ($expenseCategories as $category) {
            $this->addSql(
                'INSERT INTO categories (name, type, icon_id, status) VALUES (?, ?, ?, ?)',
                [$category['name'], $category['type'], $category['icon_id'], $category['status']]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM categories');
    }
}
