<?php

namespace Tests\Util;
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
    public function deleteAllRecords($tables, $pdo){
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("DELETE FROM $table");
            $stmt->execute();
        }
    }
}