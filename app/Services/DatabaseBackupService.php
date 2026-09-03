<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DatabaseBackupService
{
    /**
     * Create a backup of the database and save it in storage/app/backups.
     *
     * @return string Filename of the generated backup
     */
    public function backup(): string
    {
        // Increase limits for large database operations
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $driver = DB::connection()->getDriverName();
        $dbName = config('database.connections.mysql.database') ?: DB::connection()->getDatabaseName();
        $safeDbName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $dbName);
        $fileName = 'backup_' . $safeDbName . '_' . now()->format('Ymd_His') . '.sql';

        if (!Storage::exists('backups')) {
            Storage::makeDirectory('backups');
        }

        $path = Storage::path('backups/' . $fileName);
        $handle = fopen($path, 'w+');

        fwrite($handle, "-- Uteparts Database Backup\n");
        fwrite($handle, "-- Database: {$dbName}\n");
        fwrite($handle, "-- Driver: {$driver}\n");
        fwrite($handle, "-- Generated: " . now()->toDateTimeString() . "\n\n");

        if ($driver === 'sqlite') {
            fwrite($handle, "PRAGMA foreign_keys = OFF;\n\n");
        } else {
            fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($handle, "SET time_zone = \"+00:00\";\n\n");
        }

        // Get all tables
        if ($driver === 'sqlite') {
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        } else {
            $tables = DB::select('SHOW FULL TABLES');
        }

        $pdo = DB::getPdo();

        foreach ($tables as $table) {
            $tableVars = get_object_vars($table);
            $tableName = (string) reset($tableVars);
            $tableType = count($tableVars) > 1 ? (string) next($tableVars) : 'BASE TABLE';

            if ($driver !== 'sqlite' && strtoupper($tableType) === 'VIEW') {
                continue;
            }

            fwrite($handle, "-- --------------------------------------------------------\n");
            fwrite($handle, "-- Table structure for `$tableName`\n");
            fwrite($handle, "-- --------------------------------------------------------\n\n");
            
            fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");

            // Fetch table creation syntax
            if ($driver === 'sqlite') {
                $createTableResult = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$tableName]);
                $createSql = !empty($createTableResult) ? $createTableResult[0]->sql : null;
            } else {
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $createSql = $createTable[0]->{'Create Table'} ?? $createTable[0]->{'Create View'} ?? null;
            }

            if (!$createSql) {
                continue;
            }

            fwrite($handle, $createSql . ";\n\n");

            // In SQLite, dump table indexes immediately so foreign key constraints and unique checks are satisfied
            if ($driver === 'sqlite') {
                $tableIndexes = DB::select("SELECT sql FROM sqlite_master WHERE type='index' AND tbl_name = ? AND sql IS NOT NULL AND name NOT LIKE 'sqlite_%'", [$tableName]);
                foreach ($tableIndexes as $tIndex) {
                    if (!empty($tIndex->sql)) {
                        fwrite($handle, $tIndex->sql . ";\n");
                    }
                }
                if (!empty($tableIndexes)) {
                    fwrite($handle, "\n");
                }
            }

            // Dump data
            fwrite($handle, "-- Dumping data for table `{$tableName}`\n\n");

            $columns = Schema::getColumnListing($tableName);
            if (empty($columns)) {
                continue;
            }
            $escapedColumns = array_map(function ($col) {
                return "`$col`";
            }, $columns);
            $columnsStr = implode(', ', $escapedColumns);

            // Write inserts in buffered groups of 100 rows using lazy cursor
            $buffer = [];
            $firstCol = $columns[0];
            foreach (DB::table($tableName)->orderBy($firstCol, 'asc')->cursor() as $row) {
                $values = [];
                foreach ((array) $row as $val) {
                    if ($val === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = $pdo->quote((string) $val);
                    }
                }
                $buffer[] = "(" . implode(", ", $values) . ")";

                if (count($buffer) >= 100) {
                    fwrite($handle, "INSERT INTO `{$tableName}` ($columnsStr) VALUES \n" . implode(",\n", $buffer) . ";\n\n");
                    $buffer = [];
                }
            }

            if (count($buffer) > 0) {
                fwrite($handle, "INSERT INTO `{$tableName}` ($columnsStr) VALUES \n" . implode(",\n", $buffer) . ";\n\n");
            }
        }

        if ($driver === 'sqlite') {
            fwrite($handle, "PRAGMA foreign_keys = ON;\n");
        } else {
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        }
        fclose($handle);

        return $fileName;
    }

    /**
     * Restore the database using a backup file.
     *
     * @param string $fileName
     * @return void
     */
    public function restore(string $fileName): void
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $path = Storage::path('backups/' . $fileName);
        if (!file_exists($path)) {
            throw new \Exception("File backup tidak ditemukan.");
        }

        $driver = DB::connection()->getDriverName();

        // Disable foreign key checks
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            try {
                DB::getPdo()->exec('PRAGMA foreign_keys = OFF;');
            } catch (\Throwable $e) {}
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        $handle = fopen($path, 'r');
        $query = '';

        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
                continue;
            }

            $query .= $line;

            if (str_ends_with(rtrim($line), ';')) {
                DB::unprepared($query);
                $query = '';
            }
        }

        fclose($handle);

        // Re-enable foreign key checks
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
            try {
                DB::getPdo()->exec('PRAGMA foreign_keys = ON;');
            } catch (\Throwable $e) {}
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
