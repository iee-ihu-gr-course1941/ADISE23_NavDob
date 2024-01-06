<?php

namespace src\Model;

$config = require_once 'config.php';

class GameModel {

    private $pdo;

    public function __construct() {
        $this->initDatabaseConnection();
    }

    private function initDatabaseConnection() {
        $config = include 'config.php';

        try {
            $this->pdo = new \PDO(
                "mysql:host=" . $config['host'] . ";dbname=" . $config['db'] . ";charset=" . $config['charset'],
                $config['user'],
                $config['pass']
            );
        } catch (\PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }

    public function initializeGame() {
        $this->initializePlayerBoard(1);
        $this->initializePlayerBoard(2);
    }

    private function initializePlayerBoard($player) {
        // Initialize player board with empty spaces
        $board = array_fill(0, 10, array_fill(0, 10, 0));

        // Allow the player to place each battleship
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
                // Get user input for ship position
                $position = strtoupper(trim(readline("Enter the position for space " . ($i + 1) . ": ")));

                // Convert position to coordinates (e.g., "A5" becomes [0, 4])
                $coordinates = $this->convertPositionToCoordinates($position);

                // Check if the position is valid
                $isValid = $this->isValidPosition($coordinates, $board);

                if (!$isValid) {
                    echo "Invalid position. Please try again.\n";
                }
            } while (!$isValid);

            // Mark the ship on the board
            $board[$coordinates[0]][$coordinates[1]] = $shipSize;
        }
    }

    private function convertPositionToCoordinates($position) {
        // Convert position (e.g., "A5") to coordinates [0, 4]
        $column = ord($position[0]) - ord('A');
        $row = intval(substr($position, 1)) - 1;

        return [$row, $column];
    }

    private function isValidPosition($coordinates, $board) {
        // Check if the position is within the board boundaries and is not already occupied
        $row = $coordinates[0];
        $column = $coordinates[1];

        return $row >= 0 && $row < 10 && $column >= 0 && $column < 10 && $board[$row][$column] === 0;
    }
}
