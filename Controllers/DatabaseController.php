<?php

class DatabaseController extends Controller {
    /** Download the current MySQL database as a portable SQL snapshot. */
    public function export() {
        if (!Auth::hasRole('admin') || !Auth::hasPermission('manage_users')) {
            http_response_code(403);
            $this->view('errors/403');
            return;
        }

        $db = Database::getInstance()->getConnection();
        $databaseName = (string) $db->query('SELECT DATABASE()')->fetchColumn();
        $tables = $db->query('SHOW FULL TABLES')->fetchAll(PDO::FETCH_NUM);
        $filename = 'school_management_backup_' . date('Ymd_His') . '.sql';

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/sql; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('X-Content-Type-Options: nosniff');

        echo "-- School Management System database export\n";
        echo '-- Generated: ' . date('Y-m-d H:i:s') . "\n";
        echo "-- Import into an empty MySQL database. This file contains personal data and password hashes.\n\n";
        echo "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n";
        echo 'CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '``', $databaseName) . "` CHARACTER SET utf8mb4;\n";
        echo 'USE `' . str_replace('`', '``', $databaseName) . "`;\n\n";

        foreach ($tables as $tableInfo) {
            // SHOW FULL TABLES returns [name, type]; skip views and other objects.
            if (strtoupper((string) ($tableInfo[1] ?? '')) !== 'BASE TABLE') {
                continue;
            }

            $tableName = (string) $tableInfo[0];
            $quotedTable = '`' . str_replace('`', '``', $tableName) . '`';
            $create = $db->query('SHOW CREATE TABLE ' . $quotedTable)->fetch(PDO::FETCH_NUM);
            if (!$create || empty($create[1])) {
                continue;
            }

            echo "-- Table: {$tableName}\n";
            echo 'CREATE TABLE IF NOT EXISTS ' . substr((string) $create[1], strlen('CREATE TABLE ')) . ";\n";

            $columns = array_map(function ($column) {
                return '`' . str_replace('`', '``', (string) $column['Field']) . '`';
            }, $db->query('SHOW COLUMNS FROM ' . $quotedTable)->fetchAll(PDO::FETCH_ASSOC));
            $statement = $db->query('SELECT * FROM ' . $quotedTable, PDO::FETCH_NUM);

            if ($columns !== []) {
                $columnList = implode(', ', $columns);
                while ($row = $statement->fetch(PDO::FETCH_NUM)) {
                    $values = [];
                    foreach ($row as $value) {
                        $values[] = $value === null ? 'NULL' : $db->quote((string) $value);
                    }
                    echo 'INSERT INTO ' . $quotedTable . ' (' . $columnList . ') VALUES ('
                        . implode(', ', $values) . ");\n";
                }
            }
            $statement->closeCursor();
            echo "\n";
            flush();
        }

        echo "SET FOREIGN_KEY_CHECKS=1;\n";
        exit;
    }
}
