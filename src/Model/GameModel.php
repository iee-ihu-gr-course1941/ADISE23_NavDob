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

    private function savePlayerBoard($player, $ships) {
        $boardJson = json_encode($ships);
    
        // Build the VALUES part of the SQL query for all cells
        $values = '';
        foreach ($ships as $rowIndex => $row) {
            foreach ($row as $colIndex => $cell) {
                $coordinate = chr(ord('A') + $colIndex) . ($rowIndex + 1);
    
                if (is_array($cell)) {
                    $status = $this->getStatusValue($cell['status']);
                } else {
                    // Handle the case when $cell is not an array
                    $status = $this->getStatusValue($cell);
                }
    
                $values .= "($player, '$coordinate', '$status', '$boardJson'),";
            }
        }
    
        // Remove the trailing comma from the values string
        $values = rtrim($values, ',');
    
        // Build the full SQL query
        $query = "INSERT INTO boards (player_id, coordinate, status, board_state) VALUES $values
                  ON DUPLICATE KEY UPDATE status = VALUES(status), board_state = VALUES(board_state)";
    
        // Execute the query
        $result = $this->mysqli->query($query);
    
        if (!$result) {
            throw new Exception('Error in query: ' . $this->mysqli->error);
        }
    }
    
    /**
     * Get the valid status value for the ENUM column.
     * @param string $status The status value to check.
     * @return string The valid status value.
     */
    private function getStatusValue($status) {
        $validStatusValues = ['empty', 'ship', 'hit', 'miss'];
        return in_array($status, $validStatusValues) ? $status : 'empty';
    }
              
    /**
     * Get the game board for a specific player from the database.
     * @param int $playerId The ID of the player.
     * @return array The game board for the player.
     */
    public function getPlayerBoard($playerId) {
        $query = "SELECT player_id, coordinate, status, board_state FROM boards WHERE player_id = ?";
        $statement = $this->mysqli->prepare($query);
        $statement->bind_param('i', $playerId);
        $statement->execute();
        $statement->bind_result($playerId, $coordinate, $status, $boardState);

        // Create an array to represent the game board
        $board = array_fill(0, 10, array_fill(0, 10, 0));

        // Fetch each row and update the game board
        while ($statement->fetch()) {
            // Update the board based on retrieved data
            list($row, $column) = $this->convertPositionToCoordinates($coordinate);
            $board[$row][$column] = ['status' => $status, 'board_state' => $boardState];
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
     * @param array $board The game board.
     * @return bool True if the board is empty, false otherwise.
     */
    private function isBoardEmpty($board) {
        // Check if the board is empty based on your specific criteria
        // For example, check if all elements are 0
        return array_reduce($board, 'array_merge', []) === array_fill(0, 100, 0);
    }

    /**
     * Make a move on the game board.
     * @param int    $playerId The ID of the player making the move.
     * @param string $position The position for the move (e.g., A5).
     * @return bool True if the move is valid, false otherwise.
     */
    public function makeMove($player, $position) {
        $coordinates = $this->convertPositionToCoordinates($position);

        // Check if the move is valid
        if (!$this->isValidPosition($coordinates, $this->getPlayerBoard($player))) {
            return false;
        }

        // Get the current player's board
        $board = $this->getPlayerBoard($player);

        // Update the board based on the move
        $status = $this->updateBoard($board, $coordinates);

        // Save the updated board to the database
        $this->savePlayerBoard($player, $board);

        // Check if the move resulted in a hit
        return $status === 'hit';
    }

    private function updateBoard(&$board, $coordinates) {
        $row = $coordinates[0];
        $column = $coordinates[1];

        // Check the status of the cell
        $cell = $board[$row][$column];

        if ($cell['status'] === 'empty') {
            // The move is a miss
            $board[$row][$column]['status'] = 'miss';
            $status = 'miss';
        } elseif ($cell['status'] === 'ship') {
            // The move is a hit
            $board[$row][$column]['status'] = 'hit';
            $status = 'hit';
        } else {
            // Cell is already hit or missed
            $status = 'invalid';
        }

        return $status;
    }

    /**
     * Check if all ships for a player have been sunk.
     * @param int $playerId The player ID.
     * @return bool True if all ships are sunk, false otherwise.
     */
    private function areAllShipsSunk($playerId) {
        $playerBoard = $this->getPlayerBoard($playerId);

        // Iterate through the board and check if any ship is still afloat
        foreach ($playerBoard as $row) {
            foreach ($row as $cell) {
                if ($cell > 0) {
                    return false; // At least one ship is still afloat
                }
            }
        }

        return true; // All ships are sunk
    }

    /**
     * Checks if the game is over.
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
