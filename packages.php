<?php

use Knevelina\Modernity\Data\LanguageLevelTuple;
use Knevelina\Modernity\Enums\LanguageLevel;
use Knevelina\Modernity\Modernity;

require_once __DIR__ . '/vendor/autoload.php';

$dir = __DIR__ . '/src/';
if ($argc === 2) {
    $dir = $argv[1];
}
$modernity = new Modernity();

$fp = fopen(__DIR__ . '/resources/results/' . date('Ymd_His') . '.csv', 'w');

$line = ['Package', 'Version'];

$versions = LanguageLevel::range();

foreach ($versions as $version) {
    $line[] = $version->value;
}

fputcsv($fp, $line);

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    echo $errno.': '.$errstr.PHP_EOL;
    echo $errfile.':'.$errline.PHP_EOL;
    exit(1);
}, E_ALL);

foreach (new DirectoryIterator(__DIR__ . '/resources/packages/') as $package) {
    if (!$package->isDir() || $package->isDot()) {
        continue;
    }
    echo '== ' . $package . ' ==' . PHP_EOL;
    $line[0] = $package;

    foreach (new DirectoryIterator($package->getRealPath()) as $version) {
        if (!$version->isDir() || $version->isDot()) {
            continue;
        }

        $line[1] = $version;

        echo ' - ' . $version . PHP_EOL;

        $tuple = new LanguageLevelTuple();

        try {
            $tuple = $modernity->getTupleForDirectory($version->getRealPath());
        } catch (Exception|Error $e) {
            echo 'Parse error: ' . $e->getMessage() . PHP_EOL;
            exit(1);
        }

        echo '   ' . $tuple . PHP_EOL;

        for ($i = 0; $i < count($versions); $i++) {
            $line[$i + 2] = $tuple[$versions[$i]];
        }

        fputcsv($fp, $line);
    }
}

fclose($fp);