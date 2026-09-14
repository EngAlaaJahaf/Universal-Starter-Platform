<?php

class Database
{
    private static $instance = null;
    private $pdo;

    public function __construct()
    {
        $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . $charset;

        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Native prepares (SH-06): real server-side prepared statements →
            // stronger SQLi defense-in-depth. Audited: no SQL reuses a named
            // placeholder twice, so this is safe with MySQL 5.7+/8 PDO.
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        );

        $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        // Normalize: every connection stores/treats timestamps as UTC regardless of
        // the MySQL server's own timezone (local XAMPP vs. shared hosting differ).
        // Display-side helpers (fmt_date / site_dt) convert UTC to the site timezone.
        $this->pdo->exec("SET time_zone = '+00:00'");
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    public function prepare($sql)
    {
        return $this->pdo->prepare($sql);
    }

    public function query($sql, array $params = array())
    {
        $statement = $this->prepare($sql);
        $statement->execute($params);
        return $statement;
    }

    public function fetch($sql, array $params = array())
    {
        $statement = $this->query($sql, $params);
        $result = $statement->fetch();
        return $result === false ? null : $result;
    }

    public function fetchAll($sql, array $params = array())
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
}
