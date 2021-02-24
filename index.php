<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

const POINT_TO_START = 1;
const MOVE_SET_SIZE = 4;

//General fetch functions.

function FetchName($input)
{
    if (isset($input['species']['name']) && !empty($input['species']['name'])){
        return $input['species']['name'];
    }
    return 'Invalid Name';
}

function FetchPokemon($input)
{
    try {
        $json = json_decode(file_get_contents('https://pokeapi.co/api/v2/pokemon/' . $input), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        $json = null;
    }
    return $json;
}

function FetchSprite($input){
    return $input['sprites']['other']['official-artwork']['front_default'] ?? ('2.png');
}

    //Fetch on input
    if (isset($_GET['find']) && !empty($_GET['find'])) {
    $json = FetchPokemon(htmlspecialchars($_GET['find'], ENT_NOQUOTES));
}else{$json = FetchPokemon(POINT_TO_START);}
    
    $sprite = FetchSprite($json);
    $name = FetchName($json);
    $id = $json['id'] ?? '';
    $moves = $json['moves'] ?? [];
    $species = $json['species']['url'] ?? '';

    //Echo Pokemon Image && Name && Id
    function ShowCase($sprite, $id, $name)
    {
        echo '<img id="pokemon_picture" src=' . $sprite . ' alt="pokemon image"><br/>';
        echo '<div>' . '#' . str_pad($id, 3, '0', STR_PAD_LEFT) . ' ' . $name . '</div>';
    }

    //Collect & Echo move set.
    function MoveSet($moves)
    {if(isset($moves) && !empty($moves)){
        shuffle($moves);
        $size = (MOVE_SET_SIZE < count($moves)) ? MOVE_SET_SIZE : count($moves);
        array_splice($moves, $size);

        echo 'Move Set:<br/>';
        foreach ($moves AS $move) {
            echo '<div class="move_block">' . $move['move']['name'] . '</div>';
        }
    }else{ echo '<div class="move_block">No Moves Found</div>';}

    }

    //Collect & Echo evolution chain for $species.
    function Evolution($species)
    {
        if (isset($species) && !empty($species)) {
            try {
                $json = json_decode(file_get_contents($species), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {return;
            }
            try {
                $json = json_decode(file_get_contents($json['evolution_chain']['url']), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {return;
            }

            $base_form = [];
            $first_form = [];
            $second_form = [];

            //base form.

            $base_form[] = FetchPokemon(FetchName($json['chain']));

            //first & second forms.

            foreach ($json['chain']['evolves_to'] as $first) {
                $data = FetchPokemon(FetchName($first));
                $first_form[] = $data;
                foreach ($first['evolves_to'] as $second) {
                    $data = FetchPokemon(FetchName($second));
                    $second_form[] = $data;
                }
            }

            function PrintChain($target)
            {
                if (!empty($target)) {

                    //Open html div:

                    echo '<div class="evo_block">';

                    //Echo image of every evolution of that tier:

                    foreach ($target as $sprite) {
                        $title = FetchName($sprite);
                        if ($title === POINT_TO_START) {
                            $title = 'Invalid';
                        }
                        echo '<a href=http://pokedex.local/index.php?find=' . FetchName($sprite) . ' title=' . $title . '>';
                        echo '<img class="evolink" alt="" src=' . FetchSprite($sprite) . '>';
                        echo '</a>';
                    }

                    //Close html div.
                    echo '</div>';

                }else{ echo '<div><p>No Pokemon Found</p></div>';}
            }

            PrintChain($base_form);
            PrintChain($first_form);
            PrintChain($second_form);
        }else{ echo '<div><p>No Pokemon Found</p></div>';}
    }
    
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pokédex</title>
    <link rel="stylesheet" href="pokemon.css">
    <link rel="shortcut icon" href="#"/>
</head>
<body>

<form action="" method="get" class="searchbar">
    <label for="find"></label>
    <input type="text" id="find" name="find" placeholder="Enter Name or ID">
    <input type="submit" id="run" value="Find this pokemon">
</form>
<div class="container">
    <div class="card card1">
        <div id="window">
            <?php
                ShowCase($sprite, $id, $name);
            ?>
        </div>

        <div class="move_set">
            <?php
                MoveSet($moves);
            ?>
        </div>
    </div>
</div>
<div class="container">
    <div class="card card2">
        <div id="evochain">
            <?php
                Evolution($species);
            ?>
        </div>
    </div>
</div>
</body>