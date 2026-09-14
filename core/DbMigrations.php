<?php

/**
 * DbMigrations — ledger-based schema migration runner (SH-10).
 *
 * Pure helpers (no DB side effects) so the ordering / pending diff / statement
 * splitting logic is unit-testable without MySQL. The `craft db:migrate`
 * command drives the actual application + ledger.
 *
 * Conventions:
 *   - Migration files live in database/, named migrations*.sql
 *     (migrations.sql + optional migrations_*.sql).
 *   - schema.sql / seed.sql are imported separately and never run by this tool.
 *   - Applied files are recorded in the schema_migrations table (PK = filename).
 */

class DbMigrations
{
    /** Directory containing the SQL migration files. */
    public static function dir()
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database';
    }

    /**
     * Ordered migration filenames (basename, sorted ascending).
     * Includes migrations.sql plus extra migrations_*.sql files; excludes
     * schema.sql and seed.sql.
     */
    public static function files()
    {
        $glob = glob(self::dir() . DIRECTORY_SEPARATOR . 'migrations*.sql');
        $files = [];
        foreach ($glob ?: [] as $path) {
            $base = basename($path);
            if ($base === 'schema.sql' || $base === 'seed.sql') {
                continue;
            }
            $files[] = $base;
        }
        sort($files, SORT_STRING);
        return $files;
    }

    /** Which migration filenames still need to run, given the applied set. */
    public static function pending(array $allFiles, array $appliedFiles)
    {
        $applied = array_flip(array_map('strval', $appliedFiles));
        $pending = [];
        foreach ($allFiles as $f) {
            if (!isset($applied[$f])) {
                $pending[] = $f;
            }
        }
        return $pending;
    }

    /**
     * Split a .sql file into individual statements.
     * Handles multi-line CREATE TABLE blocks (statements terminated by a
     * trailing ';' at end-of-line) and ignores pure-comment/whitespace chunks.
     */
    public static function splitStatements($sql)
    {
        $lines = preg_split('/\r?\n/', (string)$sql);
        $statements = [];
        $current = '';
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || strpos($trimmed, '--') === 0) {
                continue;
            }
            // Dump-style comments like /*!40101 SET ... */
            if (preg_match('/^\/\*!?.*\*\/$/', $trimmed)) {
                continue;
            }
            $current .= ($current === '' ? '' : "\n") . $line;
            if (substr(rtrim($line), -1) === ';') {
                $statements[] = $current;
                $current = '';
            }
        }
        if (trim($current) !== '') {
            $statements[] = $current;
        }
        return $statements;
    }

    public static function ledgerTable()
    {
        return 'schema_migrations';
    }
}