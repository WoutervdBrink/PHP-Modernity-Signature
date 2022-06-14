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

    $directory = new RecursiveDirectoryIterator($dir);
    $iterator = new RecursiveIteratorIterator($directory);
    $files = new RegexIterator($iterator, '/^.+\.php5??$/i', RecursiveRegexIterator::GET_MATCH);

    fputcsv(STDOUT, ['File', ...array_map(fn (LanguageLevel $level): string => $level->value, LanguageLevel::range())]);

    foreach ($files as $matches) {
        $result = [$file = $matches[0]];

        $tuple = $modernity->getTupleForFile($file);

        foreach ($tuple as $level => $value) {
            $result[] = $value;
        }

        fputcsv(STDOUT, $result);
    }
} catch (\PhpParser\Error $e) {
    echo 'Parse error: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}