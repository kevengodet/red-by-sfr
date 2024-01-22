<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use Keven\RedBySfr\Client;

$client = new Client('red@keven.fr', 'red@keven.FR00', '2776735862');
foreach ($client->invoices() as $k => $v) {
    var_dump("$k => $v\n");
}