<?php

require_once dirname(dirname(__FILE__)).'/vendor/autoload.php';

use Net\Repository\SQLiteDB;

class Migration {
    public function up()
    {
        $sqlDb = new SQLiteDB(dirname(dirname(__FILE__)).'/data/sqlite.db');
        $sql = <<<SQL
CREATE TABLE groups (
    id INTEGER PRIMARY KEY AUTOINCREMENT, 
    group_name TEXT,  
    can_post INTEGER,
    article_count INTEGER
)
SQL;
        $sqlDb->query($sql);

        $sql = <<<SQL
CREATE TABLE articles (
    article_id INTEGER,
    group_id INTEGER,
    head TEXT,
    body TEXT,
    subject TEXT,
    message_id TEXT,
    has_nzb_file INTEGER
)
SQL;
        $sqlDb->query($sql);

        $sql = <<<SQL
CREATE TABLE config (
  key TEXT,
  value TEXT
)
SQL;
        $sqlDb->query($sql);

        $sql = <<<SQL
CREATE UNIQUE INDEX config_key_uindex ON config (key);
SQL;
        $sqlDb->query($sql);
    }

    public function down()
    {
        $sqlDb = new SQLiteDB(dirname(dirname(__FILE__)).'/data/sqlite.db');
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
