<?php

namespace src\View;

class GameView {
    /**
     * Displays a message to the user.
     *
     * @param string $message The message to display.
     */
    public function displayMessage($message) {
        echo $message . PHP_EOL;
    }

    /**
     * Displays the game board.
     *
     * @param array $board The game board to display.
     */
    public function displayGameBoard(array $board) {
        for ($i = 0; $i < 10; $i++) {
            for ($j = 0; $j < 10; $j++) {
                echo $board[$i][$j] . ' ';
            }
            echo "\n";
        }

        echo "\n";
    }

    /**
     * Displays a message indicating an invalid move.
     */
    public function displayInvalidMoveMessage() {
        $this->displayMessage("Invalid move. Please try again.");
    }
}
