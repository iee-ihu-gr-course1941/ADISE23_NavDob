<?php

namespace src\Model;

$config = require_once 'config.php';

class GameModel {

    private $mysqli;

    public function __construct($config) {
        $this->mysqli = $config['mysqli'];
    }

    public function initializeGame() {
        $this->initializePlayerBoard(1);
        $this->initializePlayerBoard(2);
    }

    private function initializePlayerBoard($player) {
        $board = array_fill(0, 10, array_fill(0, 10, 0));

        $this->placeShipManually($board, 5, $player, "Aircraft Carrier");
        $this->placeShipManually($board, 4, $player, "Battleship");
        $this->placeShipManually($board, 3, $player, "Cruiser");
        $this->placeShipManually($board, 3, $player, "Submarine");
        $this->placeShipManually($board, 2, $player, "Destroyer");

        $this->savePlayerBoard($player, $board);
    }

    private function placeShipManually(&$board, $shipSize, $player, $shipName) {
        echo "Player $player, place your $shipName ($shipSize spaces).\n";

        for ($i = 0; $i < $shipSize; $i++) {
            do {
                $position = strtoupper(trim(readline("Enter the position for space " . ($i + 1) . ": ")));
                $coordinates = $this->convertPositionToCoordinates($position);
                $isValid = $this->isValidPosition($coordinates, $board);

                if (!$isValid) {
                    echo "Invalid position. Please try again.\n";
                }
            } while (!$isValid);

            $board[$coordinates[0]][$coordinates[1]] = $shipSize;
        }
    }

    private function convertPositionToCoordinates($position) {
        $column = ord($position[0]) - ord('A');
        $row = intval(substr($position, 1)) - 1;
        return [$row, $column];
    }

    private function isValidPosition($coordinates, $board) {
        $row = $coordinates[0];
        $column = $coordinates[1];

        return $row >= 0 && $row < 10 && $column >= 0 && $column < 10 && $board[$row][$column] === 0;
    }

    private function savePlayerBoard($player, $board) {
        $boardJson = json_encode($board);
    
        $query = "INSERT INTO boards (player_id, coordinate, status, board_state) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = ?, board_state = ?";
        $statement = $this->mysqli->prepare($query);
    
        foreach ($board as $rowIndex => $row) {
            foreach ($row as $colIndex => $cell) {
                $coordinate = chr(ord('A') + $colIndex) . ($rowIndex + 1);
                $statement->bind_param('ississ', $player, $coordinate, $cell['status'], $boardJson, $cell['status'], $boardJson);
                $statement->execute();
            }
        }
    }      

    /**
     * Get the game board for a specific player from the database.
     *
     * @param int $playerId The ID of the player.
     * @return array The game board for the player.
     */
    public function getPlayerBoard($playerId) {
        // Assuming you have a 'boards' table with columns 'position_x', 'position_y', 'status', and 'board_state'
        $query = "SELECT position_x, position_y, status, board_state FROM boards WHERE player_id = ?";
        $statement = $this->mysqli->prepare($query);
        $statement->bind_param('i', $playerId);
        $statement->execute();
        $statement->bind_result($positionX, $positionY, $status, $boardState);

        // Create an array to represent the game board
        $board = array_fill(0, 10, array_fill(0, 10, 0));

        // Fetch each row and update the game board
        while ($statement->fetch()) {
            // Update the board based on retrieved data
            $board[$positionX][$positionY] = ['status' => $status, 'board_state' => $boardState];
        }

        $statement->close();

        // If the board is empty (no data in the database), initialize it
        if ($this->isBoardEmpty($board)) {
            $this->initializePlayerBoard($playerId, $board);
        }

        return $board;
    }

    /**
     * Check if the board is empty (no data in the database).
     *
     * @param array $board The game board.
     * @return bool True if the board is empty, false otherwise.
     */
    private function isBoardEmpty($board) {
        // Check if the board is empty based on your specific criteria
        // For example, check if all elements are 0
        return array_reduce($board, 'array_merge', []) === array_fill(0, 100, 0);
    }

    /**
     * Check if all ships for a player have been sunk.
     *
     * @param int $playerId The player ID.
     * @return bool True if all ships are sunk, false otherwise.
     */
    private function areAllShipsSunk($playerId) {
        $playerBoard = $this->getPlayerBoard($playerId);

        // Iterate through the board and check if any ship is still afloat
        foreach ($playerBoard as $row) {
            foreach ($row as $cell) {
                if ($cell === 'ship') {
                    return false; // At least one ship is still afloat
                }
            }
        }

        return true; // All ships are sunk
    }

    /**
     * Checks if the game is over.
     *
     * @return bool True if the game is over, false otherwise.
     */
    public function isGameOver() {
        // Check if all ships for a players are sunk
        $allShipsSunkPlayer1 = $this->areAllShipsSunk(1);
        $allShipsSunkPlayer2 = $this->areAllShipsSunk(2);

        return $allShipsSunkPlayer1 || $allShipsSunkPlayer2;
    }

    /**
     * Get the winner of the game.
     *
     * @return int|null The player ID of the winner, or null if there is no winner.
     */
    public function getWinner() {
        $playerIds = [1, 2];

        foreach ($playerIds as $playerId) {
            if ($this->areAllShipsSunk($playerId)) {
                return $playerId;
            }
        }

        return null; // No winner yet
    }
}
