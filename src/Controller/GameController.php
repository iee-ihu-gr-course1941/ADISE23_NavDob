<?php

namespace src\Controller;

use src\Model\GameModel;
use src\View\GameView;

class GameController {
    private $gameModel;
    private $gameView;

    public function __construct(GameModel $gameModel) {
        $this->gameModel = $gameModel;
        $this->gameView = new GameView();
    }

    /**
     * Starts the game.
     */
    public function startGame() {
        $this->gameView->displayMessage("Welcome to Navmaxia Sea Battle!");

        $this->initializeGame();

        while (!$this->gameOver()) {
            $this->gameView->displayMessage("Game Board:");
            $this->gameView->displayGameBoard($this->gameModel->getBoard(1)); // Player 1 board

            $this->processPlayerMove(1); // Player 1 turn

            $this->gameView->displayMessage("Game Board:");
            $this->gameView->displayGameBoard($this->gameModel->getBoard(2)); // Player 2 board

            $this->processPlayerMove(2); // Player 2 turn
        }

        // Display the result of the game
        $this->gameView->displayMessage("Game Over. " . $this->getGameResult());
    }

    /**
     * Initializes the game by placing ships for both players.
     */
    private function initializeGame() {
        $this->gameModel->initializeGame();
    }

    /**
     * Processes a player's move.
     *
     * @param int $player The player making the move.
     */
    private function processPlayerMove($player) {
        // Logic to handle a player's move
        $isValidMove = false;

        while (!$isValidMove) {
            $position = strtoupper(trim(readline("Player $player, enter your move (e.g., A5): ")));
            $isValidMove = $this->gameModel->makeMove($player, $position);

            if (!$isValidMove) {
                $this->gameView->displayInvalidMoveMessage();
            }
        }
    }

    /**
     * Checks if the game is over.
     *
     * @return bool True if the game is over, false otherwise.
     */
    private function gameOver() {
        return $this->gameModel->isGameOver();
    }

    /**
     * Gets the result of the game.
     *
     * @return string The result of the game (winner, loser, tie, etc.).
     */
    private function getGameResult() {
        $winner = $this->gameModel->getWinner();

        if ($winner !== null) {
            return "Player $winner wins!";
        } else {
            return "It's a tie!";
        }
    }
}
