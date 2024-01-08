<?php

require_once 'vendor/autoload.php';

use src\Controller\GameController;
use src\Model\GameModel;

// Instantiate the necessary objects
$model = new GameModel();
$controller = new GameController($model);

// Start the game
$controller->startGame();
