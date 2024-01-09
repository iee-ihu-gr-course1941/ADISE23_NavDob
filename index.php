<?php

require_once __DIR__ . '/vendor/autoload.php';
include 'config.php';

use src\Controller\GameController;
use src\Model\GameModel;

// Instantiate the necessary objects
$model = new GameModel($config);
$controller = new GameController($model);

// Start the game
$controller->startGame();