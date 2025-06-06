<?php

declare(strict_types=1);

namespace Acc\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250601184618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migração para inserir dados iniciais na tabela de ícones (nomes e arquivos em português)';
    }

    public function up(Schema $schema): void
    {
        $icones = [
            ['name' => 'dinheiro',     'color'=> '#4CAF50', 'icon_file' => 'dinheiro.svg',     'status' => true],
            ['name' => 'despesa',      'color' => '#F44336', 'icon_file' => 'despesa.svg',      'status' => true],
            ['name' => 'receita',      'color' => '#2196F3', 'icon_file' => 'receita.svg',      'status' => true],
            ['name' => 'poupança',     'color' => '#FF9800', 'icon_file' => 'poupanca.svg',     'status' => true],
            ['name' => 'orçamento',    'color' => '#9C27B0', 'icon_file' => 'orcamento.svg',    'status' => true],
            ['name' => 'compras',      'color' => '#795548', 'icon_file' => 'compras.svg',      'status' => true],
            ['name' => 'viagem',       'color' => '#00BCD4', 'icon_file' => 'viagem.svg',       'status' => true],
            ['name' => 'alimentação',  'color' => '#8BC34A', 'icon_file' => 'alimentacao.svg',  'status' => true],
            ['name' => 'saúde',        'color' => '#E91E63', 'icon_file' => 'saude.svg',        'status' => true],
            ['name' => 'transporte',   'color' => '#3F51B5', 'icon_file' => 'transporte.svg',   'status' => true],
            ['name' => 'educação',     'color' => '#FFC107', 'icon_file' => 'educacao.svg',     'status' => true],
        ];

        foreach ($icones as $icone) {
            $this->addSql(
                'INSERT INTO icons (name, color, icon_file, status) VALUES (?, ?, ?, ?)',
                [
                    $icone['name'],
                    $icone['color'],
                    $icone['icon_file'],
                    $icone['status']
                ]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql("TRUNCATE TABLE icons");
    }
}
