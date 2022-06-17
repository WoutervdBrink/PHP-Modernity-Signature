<?php

use Knevelina\Modernity\Enums\LanguageLevel;
use Knevelina\Modernity\Modernity;

require_once __DIR__ . '/vendor/autoload.php';

try {
    $dir = __DIR__.'/src/';
    if ($argc === 2) {
        $dir = $argv[1];
    }
    $modernity = new Modernity();

    echo $modernity->getTupleForDirectory($dir);
} catch (\PhpParser\Error $e) {
    echo 'Parse error: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}