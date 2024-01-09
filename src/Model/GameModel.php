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

        $query = "INSERT INTO boards (player_id, board_state) VALUES (?, ?)";
        $statement = $this->mysqli->prepare($query);
        $statement->bind_param('is', $player, $boardJson);
        $statement->execute();
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
}
