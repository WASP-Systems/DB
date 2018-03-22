<?php

use Wedeto\DB\Query\Builder AS QB;
use Wedeto\DB\Schema\Table;
use Wedeto\DB\Schema\Column;
use Wedeto\DB\Schema\Index;

$table = new Table(
    "db_version",
    new Column\Serial('id'),
    new Column\Varchar('module', 128),
    new Column\Integer('version'),
    new Column\Datetime('date_upgraded'),
    new Index(Index::PRIMARY, 'id'),
    new Index(Index::UNIQUE, 'module', 'version')
);

$db->getDriver()->createTable($table);
