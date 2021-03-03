<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pokédex</title>
    <link rel="stylesheet" href="pokedex-stylesheet.css">
</head>
<body>

<div class="container">
    <form method="get" class="spaceHolder">
        <label for="find"></label>
        <input type="text" id="find" name="find">
        <input type="submit" id="search" class="case" value="">
    </form>
    <div class="buffer"></div>
    <div class="statCase">
        <div class="slider">
            <?php slideButton($pokeData ?? '0', 'prev'); ?>
            <button id="previous" class="sliderButton sliderComp"> <</button>
            <div class="showCase sliderComp">
                <?php
                echo $pokemonSprite ?? ''
                ?>
            </div>
            <?php slideButton($pokeData ?? '0', 'next'); ?>
            <button id="next" class="sliderButton sliderComp"> ></button>

        </div>
        <div class="nameTag" id="showTag">
            <?php
            echo $pokemonTag ?? ''
            ?>
        </div>
        <div class="moveSet" id="moveContainer">
            <?php
            catchError();
            printMoveSet($pokeData['moves'] ?? []);
            ?>
        </div>
        <div class="evoSet">
            <?php
            printEvoChain($evoData ?? []);
            ?>
        </div>
    </div>
</div>

</body>
</html>