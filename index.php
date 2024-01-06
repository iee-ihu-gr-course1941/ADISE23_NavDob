<?php

require_once 'vendor/autoload.php';

use src\Controller\GameController;

// Check if the game is already in progress
if (!isset($_SESSION['game_in_progress']) || !$_SESSION['game_in_progress']) {
    // Set the game in progress flag to avoid restarting the game on page refresh
    $_SESSION['game_in_progress'] = true;

    // Create an instance of GameController
    $gameController = new GameController();

    // Start the game
    $gameController->startGame();
} else {
    echo "The game is already in progress.";
}
