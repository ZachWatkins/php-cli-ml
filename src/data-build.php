<?php
const FILE_TRAINING_DATA = 'data/training.csv';
const FILE_TESTING_DATA = 'data/testing.csv';
const FILE_POKEMON_DATA = 'data/pokemon.csv';
const HEADERS = ['species', 'name', 'habitat', 'egg_groups', 'growth_rate', 'color', 'height', 'weight', 'shape', 'is_baby', 'is_legendary', 'is_mythical'];

/**
 * Extract a subset of Pokemon data to a CSV file.
 * @author Zachary K. Watkins
 * @license MIT
 */

$pokemon = json_decode(file_get_contents('data/pokemon.json'), true);
$length = count($pokemon);

function training(array $pokemon): bool
{
    $species_name = $pokemon['species']['name'];
    if ($pokemon['species']['data']['habitat']) {
        $excluded = ['gmax', 'mega', 'mega-x', 'mega-y'];
        if (in_array(substr($pokemon['name'], strlen($species_name) + 1), $excluded)) {
            return false;
        }
        if ($pokemon['name'] === "{$species_name}-normal") {
            return true;
        }
        return true;
    }
    return false;
}

function testing(array $pokemon): bool
{
    $species_name = $pokemon['species']['name'];
    if ($pokemon['name'] !== $species_name) {
        $excluded = ['gmax', 'mega', 'mega-x', 'mega-y'];
        if (in_array(substr($pokemon['name'], strlen($species_name) + 1), $excluded)) {
            return false;
        }
        if ($pokemon['name'] === "{$species_name}-normal") {
            return true;
        }
        if (!$pokemon['species']['data']['is_legendary'] && !$pokemon['species']['data']['is_mythical']) {
            return true;
        }
        return false;
    }
    if (!$pokemon['order']) {
        return false;
    }
    return true;
}

$training = [];
$testing = [];
$all = [];

for ($i = 0; $i < $length; $i++) {
    $mon = json_decode(file_get_contents("data/pokemon/{$pokemon[$i]['name']}.json"), true);
    $species = $mon['species']['data'];
    $values = [
        $mon['species']['name'],
        $mon['name'],
        (isset($species['habitat']) ? $species['habitat']['name'] : '(no habitat)'),
        (isset($species['egg_groups']) ? implode(',', array_map(function ($group) {
            return $group['name'];
        }, $mon['species']['data']['egg_groups'])) : '(no egg groups)'),
        (isset($species['growth_rate']) ? $species['growth_rate']['name'] : '(no growth rate)'),
        $species['color']['name'],
        $mon['height'],
        $mon['weight'],
        (isset($species['shape']['name']) ? $species['shape']['name'] : '(no shape)'),
        ($species['is_baby'] ? 'true' : 'false'),
        ($species['is_legendary'] ? 'true' : 'false'),
        ($species['is_mythical'] ? 'true' : 'false')
    ];
    $all[] = $values;
    if (training($mon)) {
        $training[] = $values;
    }
    if (testing($mon)) {
        $testing[] = $values;
    }
}

$file = fopen(FILE_POKEMON_DATA, 'w');
fputcsv($file, HEADERS);
foreach ($all as $row) {
    fputcsv($file, $row);
}
fclose($file);

$file = fopen(FILE_TRAINING_DATA, 'w');
fputcsv($file, HEADERS);
foreach ($training as $row) {
    fputcsv($file, $row);
}
fclose($file);

$file = fopen(FILE_TESTING_DATA, 'w');
fputcsv($file, HEADERS);
foreach ($testing as $row) {
    fputcsv($file, $row);
}
fclose($file);
