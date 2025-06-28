<?php

namespace UltraLIMS\Tests\Util;

trait DatabaseUtils
{
    protected function removeRelationshipIndex($tables, $pdo)
    {
        foreach ($tables as $table) {
            $stmt = $pdo->query("
                SELECT name
                FROM sqlite_master
                WHERE type = 'index'
                  AND tbl_name = '$table'
                  AND name NOT LIKE 'sqlite_autoindex%';
            ");

            $indexes = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            if (empty($indexes)) {
                return;
            }
            $dropSql = '';
            foreach ($indexes as $indexName) {
                $dropSql .= "DROP INDEX IF EXISTS '$indexName';\n";
            }

            $pdo->exec($dropSql);
        }
    }

    protected function recreateEstablishmentTable()
    {
        $this->pdo->exec("DROP TABLE estabelecimento");
        $this->pdo->exec("
        CREATE TABLE estabelecimento (
            ai_Estabelecimento INTEGER PRIMARY KEY AUTOINCREMENT,
            idEmpresa INTEGER NOT NULL,
            idEstabelecimento VARCHAR(5) NOT NULL,
            descricao VARCHAR(100),
            idPessoa INTEGER,
            ai_MenuPerfilColetor INTEGER,
            ai_MatrizTempoContato INTEGER,
            ai_EstabelecimentoAnalises INTEGER
        )");
    }
}