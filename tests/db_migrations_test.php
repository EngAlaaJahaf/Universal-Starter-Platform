<?php
/**
 * db_migrations_test — DbMigrations pure helpers (SH-10): ordered file list,
 * pending-diff against a ledger, and the SQL statement splitter.
 */
TestRunner::suite('DbMigrations');

// File listing respects ordering and exclusion rules.
$files = DbMigrations::files();
TestRunner::isTrue(in_array('migrations.sql', $files, true), 'migrations.sql is listed');
TestRunner::isTrue(in_array('migrations_sh09_indexes.sql', $files, true), 'migrations_sh09_indexes.sql is listed');
TestRunner::isTrue(in_array('migrations_sh09_queue.sql', $files, true), 'migrations_sh09_queue.sql is listed');
TestRunner::isFalse(in_array('schema.sql', $files, true), 'schema.sql excluded from migration run');
TestRunner::isFalse(in_array('seed.sql', $files, true), 'seed.sql excluded from migration run');
TestRunner::isTrue($files === array_values(array_unique($files)), 'file list unique');
TestRunner::isTrue($files === $files, 'file list sorted ascending');

// Pending diff.
$all = ['migrations.sql', 'migrations_sh09_indexes.sql', 'migrations_sh09_queue.sql'];
TestRunner::isCount(3, DbMigrations::pending($all, []), 'fresh DB: all migrations pending');
TestRunner::is(
    ['migrations_sh09_indexes.sql', 'migrations_sh09_queue.sql'],
    DbMigrations::pending($all, ['migrations.sql']),
    'applied first file leaves the rest pending, in order'
);
TestRunner::isCount(0, DbMigrations::pending($all, $all), 'all applied → nothing pending');
TestRunner::isCount(1, DbMigrations::pending($all, ['migrations.sql', 'migrations_sh09_queue.sql']), 'gap detected when queue applied before indexes');

// Statement splitter: multi-line CREATE TABLE + comments + trailing statements.
$sample = <<<'SQL'
-- Migration header comment
CREATE TABLE `foo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `x` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `bar` ADD INDEX `idx` (`x`);

/*!40101 SET character_set_client = @saved_cs_client */;
SQL;
$stmts = DbMigrations::splitStatements($sample);
TestRunner::isCount(3, $stmts, 'splitter produces N statements');
TestRunner::isTrue(strpos($stmts[0], 'CREATE TABLE `foo`') === 0, 'fat statement preserved intact (no mid-body split)');
TestRunner::isTrue(strpos($stmts[0], 'PRIMARY KEY') !== false, 'multi-line body included');
TestRunner::isTrue(strpos($stmts[1], 'ALTER TABLE') === 0, 'second statement is the ALTER');
TestRunner::isTrue(strpos($stmts[1], 'ADD INDEX') !== false, 'ALTER carries the ADD INDEX clause');
TestRunner::isTrue(strpos($stmts[2], 'SET character_set_client') !== false, 'comment-like SET statement kept');

// Inline semicolons inside strings must not split — pragmatic guard test:
$tricky = "INSERT INTO t (a) VALUES ('x;y');\nSELECT 1;";
TestRunner::isCount(2, DbMigrations::splitStatements($tricky), 'only line-terminal semicolons split');

echo "\n";
return TestRunner::summary();