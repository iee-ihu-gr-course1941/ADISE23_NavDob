<?php

namespace src\View;

class GameView {
    // Define constants for symbols
    const EMPTY_CELL = ' ';
    const SHIP_CELL = 'X';
    const HIT_CELL = '*';
    const MISS_CELL = 'O';

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
        // Display the board row by row
        foreach ($board as $row) {
            $this->displayBoardRow($row);
            echo PHP_EOL;
        }
        
        echo PHP_EOL;
    }

    /**
     * Displays a message indicating an invalid move.
     */
    public function displayInvalidMoveMessage() {
        $this->displayMessage("Invalid move. Please try again.");
    }

    /**
     * Displays a single row of the game board.
     *
     * @param array $row The row to display.
     */
    private function displayBoardRow(array $row) {
        // Display each cell in the row
        foreach ($row as $cell) {
            echo $this->getSymbolForCell($cell) . ' ';
        }
    }

    /**
     * Get the symbol for a specific cell on the game board.
     *
     * @param int|string $cell The value of the cell.
     * @return string The symbol for the cell.
     */
    private function getSymbolForCell($cell) {
        switch ($cell) {
            case 0:
                return self::EMPTY_CELL;
            case 'ship':
                return self::SHIP_CELL;
            case 'hit':
                return self::HIT_CELL;
            case 'miss':
                return self::MISS_CELL;
            default:
                // Handle other cell values if needed
                return $cell;
        }
    }
}
