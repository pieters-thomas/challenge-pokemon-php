<?php

use JetBrains\PhpStorm\Pure;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

const MOVE_SET = 4;
const API_POKEMON = 'https://pokeapi.co/api/v2/pokemon/';
const API_SPECIES = 'https://pokeapi.co/api/v2/pokemon-species/';
const LINK_NEW = '/pokemon.js/pokedex-revised/index.php?find=';
const ID_RANGE = [1, 898];

//functions

function catchError(): void
{
    if (isset($_SESSION['error']) && $_SESSION['error'] === true) {
        $_SESSION['error'] = false;
        echo 'Invalid Pokemon or ID';
    }
}

function fetchData($input, $API): array
{
    try {
        $json = json_decode(file_get_contents($API . $input), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        $_SESSION['error'] = true;
        header("Location: /pokemon.js/pokedex-revised/index.php?find=");
        exit;
    }
    return $json ?? [];
}

function fetchEvo($input): array
{
    $species = fetchData($input, API_SPECIES);

    if ($species === []) {
        return [];
    }

    try {
        $evoData = json_decode(file_get_contents($species['evolution_chain']['url']), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        $_SESSION['error'] = true;
        header("Location: /pokemon.js/pokedex-revised/index.php?find=");
        exit;
    }

    $firstStage = [fetchData(fetchName($evoData['chain']), API_POKEMON)];
    $secondStage = [];
    $thirdStage = [];

    foreach ($evoData['chain']['evolves_to'] as $evolution) {
        $data = fetchData(fetchName($evolution), API_POKEMON);
        $secondStage[] = $data;
        foreach ($evolution['evolves_to'] as $evolution2) {
            $data = fetchData(fetchName($evolution2), API_POKEMON);
            $thirdStage[] = $data;
        }
    }
    return [$firstStage, $secondStage, $thirdStage];

}

#[Pure] function fetchId($input): string
{
    if (isset($input['id']) && !empty($input['id'])) {
        return str_pad($input['id'], 3, '0', STR_PAD_LEFT);
    }
    return '000';
}

function fetchName($input): string
{
    return $input['species']['name'] ?? 'Invalid Name';
}

function fetchSprite($input): string
{
    return $input['sprites']['other']['official-artwork']['front_default'] ?? ('');
}

function printMoveSet($moves): void
{
    if (!isset($moves) && !empty($moves)) {
        echo '<div class="move">No Moves Found</div>';
        return;
    }

    $size = min(MOVE_SET, count($moves));
    shuffle($moves);
    array_splice($moves, $size);

    foreach ($moves as $move) {
        echo '<div class="move">' . $move['move']['name'] . '</div>';
    }

}

function printEvoChain($chain): void
{
    foreach ($chain as $link) {
        foreach ($link as $evoStage) {
            echo '<div class="evoSlot">'
                . '<a href=' . LINK_NEW . fetchName($evoStage) . ' class="evoLink">'
                . '<img id="showSprite" class="evoSprite" src=' . fetchSprite($evoStage) . ' alt="pokemon image">'
                . '<div class="evoTag">#' . fetchId($evoStage) . ' ' . fetchName($evoStage) . '</div>' .
                '</div>';
        }
    }

}

function slideButton($current, $direction): void
{
    //$direction: prev || next;
    if (!isset($current) || empty($current)) {
        return;
    }
    $location = (int)fetchId($current);

    switch ($direction) {

        case 'prev':
            if (($location - 1) >= ID_RANGE[0]) {
                echo '<a href=' . LINK_NEW . ($location - 1) . ' class="evoLink">';
            }
            break;
        case 'next':
            if (($location + 1) <= ID_RANGE[1]) {
                echo '<a href=' . LINK_NEW . ($location + 1) . ' class="evoLink">';
            }
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['find']) && !empty($_GET['find'])) {

    $pokemon = htmlspecialchars($_GET['find'], ENT_NOQUOTES);
    $pokeData = fetchData($pokemon, API_POKEMON);
    $evoData = fetchEvo($pokemon);

    $pokemonSprite = '<img id="showSprite" class="pokemonSprite" src=' . fetchSprite($pokeData) . ' alt="pokemon image">';
    $pokemonTag = '#' . fetchId($pokeData) . ' ' . fetchName($pokeData);

}

require 'pokedex-frame.php';
