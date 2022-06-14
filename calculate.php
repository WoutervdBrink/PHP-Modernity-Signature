<?php

use Knevelina\Modernity\Modernity;

require_once __DIR__ . '/vendor/autoload.php';

try {
    $file = __FILE__;
    if ($argc === 2) {
        $file = $argv[1];
    }
    $modernity = new Modernity();

    echo $modernity->getTupleForFile($file).PHP_EOL;
} catch (\PhpParser\Error $e) {
    echo 'Parse error: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}