<?php

/**
 * Retrieve and store Pokemon data to train a model.
 * The first url fetches a list of additional urls to fetch data from.
 */
function fetch($url): array
{
    $data = file_get_contents($url);
    $json = json_decode($data, true);
    $results = $json['results'];
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
        $pokemon = fetch('https://pokeapi.co/api/v2/pokemon/');
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

$pokemon = fetchPokemonList();

for ($i = 0; $i < count($pokemon); $i++) {
    $fetched = fetchPokemonListItem($pokemon[$i]['name'], $pokemon[$i]['url']);
    if ($fetched) {
        echo 'Fetched ' . $pokemon[$i]['name'] . PHP_EOL;
        usleep(125000);
    } else {
        echo 'Already fetched ' . $pokemon[$i]['name'] . PHP_EOL;
    }
}
