<?php

const LEVELS = ['5.2', '5.3', '5.4', '5.5', '5.6', '7.0', '7.1', '7.2', '7.3', '8.0', '8.1', '8.2'];

$fFromTest = fopen(__DIR__ . '/results/test.csv', 'r');
fgetcsv($fFromTest);

$fFromTrain = fopen(__DIR__ . '/results/train.csv', 'r');
fgetcsv($fFromTrain);

$fToTest = fopen(__DIR__ . '/resources/results/pandas_test.csv', 'w');
fputcsv($fToTest, ['Package', 'Date', 'Level', 'Value']);

$fToTrain = fopen(__DIR__ . '/resources/results/pandas_train.csv', 'w');
fputcsv($fToTrain, ['Package', 'Date', 'Level', 'Value']);

$fToAll = fopen(__DIR__ . '/resources/results/pandas.csv', 'w');
fputcsv($fToAll, ['Package', 'Date', 'Level', 'Value']);

function process($from, $to): void
{
    global $fToAll;
    while ($line = fgetcsv($from)) {
        $package = array_shift($line);
        array_shift($line);
        $date = array_shift($line);

        for ($i = 0; $i < count(LEVELS); $i++) {
            fputcsv($to, $csv = [$package, $date, LEVELS[$i], $line[$i]]);
            fputcsv($fToAll, $csv);
        }
    }
}

process($fFromTest, $fToTest);
process($fFromTrain, $fToTrain);

foreach ([$fFromTest, $fFromTrain, $fToTest, $fToTrain, $fToAll] as $fp) {
    fclose($fp);
}