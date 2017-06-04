<?php

require_once dirname(dirname(__FILE__)).'/vendor/autoload.php';

use Net\Repository\MysqlDB;

class Migration {
    public function up()
    {
        include_once dirname(__FILE__).'/../bin/credentials.php';
        $sqlDb = new MysqlDB($dsn, $mysqlUsername, $mysqlPassword);
        $sql = <<<SQL
CREATE TABLE `nntp_reader`.`groups` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_name` VARCHAR(256) NULL,
  `can_post` INT(1) NULL,
  `article_count` INT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC)
)
SQL;
        $sqlDb->exec($sql);

        $sql = <<<SQL
CREATE INDEX groups_group_name_index ON groups(group_name)
SQL;
        $sqlDb->exec($sql);

        $sql = <<<SQL
CREATE TABLE articles (
    article_id INTEGER UNSIGNED,
    group_id INTEGER UNSIGNED,
    head TEXT,
    body TEXT,
    subject TEXT,
    message_id TEXT,
    has_nzb_file INTEGER
)
SQL;
        $sqlDb->exec($sql);

        $sql = <<<SQL
CREATE INDEX articles_id_index ON articles (article_id)
SQL;
        $sqlDb->exec($sql);

        $sql = <<<SQL
CREATE TABLE config (
  `key` VARCHAR(256),
  `value` VARCHAR(256),
  UNIQUE INDEX `key_UNIQUE` (`key` ASC)
)
SQL;
        $sqlDb->exec($sql);

        $sql = <<<SQL
CREATE UNIQUE INDEX config_key_uindex ON config (key);
SQL;
        $sqlDb->exec($sql);
    }

    public function down()
    {
        include_once dirname(__FILE__).'/../bin/credentials.php';
        $sqlDb = new MysqlDB($dsn, $mysqlUsername, $mysqlPassword);

        $sql = <<<SQL
DROP TABLE articles
SQL;
        $sqlDb->query($sql);

        $sql = <<<SQL
DROP TABLE groups
SQL;
        $sqlDb->query($sql);

        $sql = <<<SQL
DROP TABLE config
SQL;

        $sqlDb->query($sql);
    }
}

$m = new Migration();

if (count($argv) > 1 && strpos($argv[1], 'direction=') === 0) {
    $direction = str_replace('direction=', '', $argv[1]);
    switch ($direction) {
        case 'up':
            $m->up();
            break;
        case 'down':
            $m->down();
            break;
        default:
            echo "Unknown direction\n";
    }
}
