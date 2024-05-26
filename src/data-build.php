<?php

/**
 * Extract a subset of Pokemon data to a CSV file.
 * @author Zachary K. Watkins
 * @license MIT
 */

$pokemon = json_decode(file_get_contents('data/pokemon.json'), true);
$length = count($pokemon);

$rows = [];
$filtered = [];

for ($i = 0; $i < $length; $i++) {
    $poke = json_decode(file_get_contents("data/pokemon/{$pokemon[$i]['name']}.json"), true);
    $species = $poke['species']['data'];
    $group = 'rows';
    if ($poke['name'] !== $species['name']) {
        $group = 'filtered';
    }
    array_push($$group, [
        $poke['species']['name'],
        $poke['name'],
        (isset($species['habitat']) ? $species['habitat']['name'] : '(no habitat)'),
        (isset($species['egg_groups']) ? implode(',', array_map(function ($group) {
            return $group['name'];
        }, $poke['species']['data']['egg_groups'])) : '(no egg groups)'),
        (isset($species['growth_rate']) ? $species['growth_rate']['name'] : '(no growth rate)'),
        $species['color']['name'],
        $poke['height'],
        $poke['weight'],
        (isset($species['shape']['name']) ? $species['shape']['name'] : '(no shape)'),
        ($species['is_baby'] ? 'true' : 'false'),
        ($species['is_legendary'] ? 'true' : 'false'),
        ($species['is_mythical'] ? 'true' : 'false')
    ]);
}

$file = fopen('data/pokemon.csv', 'w');
fputcsv($file, ['species', 'name', 'habitat', 'egg_groups', 'growth_rate', 'color', 'height', 'weight', 'shape', 'is_baby', 'is_legendary', 'is_mythical']);
foreach ($rows as $row) {
    fputcsv($file, $row);
}
fclose($file);

$file = fopen('data/filtered.csv', 'w');
fputcsv($file, ['species', 'name', 'habitat', 'egg_groups', 'growth_rate', 'color', 'height', 'weight', 'shape', 'is_baby', 'is_legendary', 'is_mythical']);
foreach ($filtered as $row) {
    fputcsv($file, $row);
}
fclose($file);
