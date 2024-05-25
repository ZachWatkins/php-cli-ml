<?php

/**
 * Retrieve and store Pokemon data to train a model.
 * The first url fetches a list of additional urls to fetch data from.
 */
function fetch(string $url, string $key = ''): array
{
    $data = file_get_contents($url);
    $json = json_decode($data, true);
    $results = $key ? $json[$key] : $json;
    if (isset($json['next']) && $json['next']) {
        return array_merge($results, fetch($json['next']));
    }
    return $results;
}

/**
 * Fetch a list of Pokemon names and their data URLs from the PokeAPI.
 * The data is stored in a file to avoid fetching it multiple times.
 */
function fetchPokemonList(): array
{
    if (!file_exists('data/pokemon.json')) {
        $pokemon = fetch('https://pokeapi.co/api/v2/pokemon/', 'results');
        if (!file_exists('data')) {
            mkdir('data');
        }
        $pokemon = json_encode($pokemon);
        $pokemon = str_replace('[{', '[' . PHP_EOL . '{', $pokemon);
        $pokemon = str_replace('},{', '},' . PHP_EOL . '{', $pokemon);
        $pokemon = str_replace('}]', '}' . PHP_EOL . ']', $pokemon);
        file_put_contents('data/pokemon.json', $pokemon);
    } else {
        $pokemon = json_decode(file_get_contents('data/pokemon.json'), true);
    }
    return $pokemon;
}

/**
 * Fetch one Pokemon's data from the PokeAPI.
 * The data is stored in a file to avoid fetching it multiple times.
 * @param string $name The name of the Pokemon to fetch.
 * @param string $url The URL to fetch the Pokemon's data from.
 * @return bool
 */
function fetchPokemonListItem(string $name, string $url): bool
{
    if (!file_exists('data/pokemon')) {
        mkdir('data/pokemon');
    }
    if (!file_exists("data/pokemon/$name.json")) {
        $data = file_get_contents($url);
        $data = json_decode($data);
        file_put_contents("data/pokemon/$name.json", json_encode($data, JSON_PRETTY_PRINT));
        return true;
    }
    return false;
}

/**
 * Fetch a Pokemon's species data from the PokeAPI.
 * The data is stored in the pokemon's existing file in the species value.
 * @param string $name The name of the Pokemon to fetch species data for.
 * @return bool
 */
function fetchPokemonSpecies(string $name): bool
{
    $filename = "data/pokemon/$name.json";
    $pokemon = json_decode(file_get_contents($filename), true);
    if (isset($pokemon['species']) && count(array_keys($pokemon['species'])) > 2) {
        return false;
    }
    $pokemon['species']['data'] = fetch($pokemon['species']['url']);
    file_put_contents($filename, json_encode($pokemon, JSON_PRETTY_PRINT));
    return true;
}

/**
 * Check if all Pokemon data has been fetched.
 * @return bool
 */
function allPokemonFetched(): bool
{
    $pokemon = fetchPokemonList();
    $length = count($pokemon);
    for ($i = 0; $i < $length; $i++) {
        if (!file_exists("data/pokemon/{$pokemon[$i]['name']}.json")) {
            return false;
        }
    }
    return true;
}

/**
 * Check if all Pokemon species data has been fetched.
 * @return bool
 */
function allPokemonSpeciesFetched(): bool
{
    $pokemon = fetchPokemonList();
    $length = count($pokemon);
    for ($i = 0; $i < $length; $i++) {
        if (fetchPokemonSpecies($pokemon[$i]['name'])) {
            return false;
        }
    }
    return true;
}

$pokemon = fetchPokemonList();
$length = count($pokemon);

if (!allPokemonFetched() || !allPokemonSpeciesFetched()) {
    echo 'Fetching Pokemon data...' . PHP_EOL;
    for ($i = 0; $i < $length; $i++) {
        $poke = $pokemon[$i];
        $base = fetchPokemonListItem($poke['name'], $poke['url']);
        if ($base) {
            echo 'Fetched base data for ' . $poke['name'] . PHP_EOL;
            usleep(125000);
        } else {
            echo 'Already fetched base data for ' . $poke['name'] . PHP_EOL;
        }
        $species = fetchPokemonSpecies($poke['name']);
        if ($species) {
            echo 'Fetched species data for ' . $poke['name'] . PHP_EOL;
            usleep(125000);
        } else {
            echo 'Already fetched species data for ' . $poke['name'] . PHP_EOL;
        }
    }
} else {
    echo 'All Pokemon data already fetched.' . PHP_EOL;
}
