<?php

$host = 'localhost';
$db = 'navmaxia';
$user = 'it185404';
$pass = '';
$charset = 'utf8mb4';

// Check if the host is 'users.iee.ihu.gr'
if (gethostname() == 'users.iee.ihu.gr') {
    $mysqli = new mysqli(
        $host,
        $user,
        $pass,
        $db,
        null,
        '/home/student/it/2018/it185404/mysql/run/mysql.sock'
    );
} else {
    // For other hosts, use a traditional connection with a password
    $mysqli = new mysqli($host, $user, $pass, $db);
}

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

return [
    'host' => $host,
    'db' => $db,
    'user' => $user,
    'pass' => $pass,
    'charset' => $charset,
    'mysqli' => $mysqli, // Return the MySQLi object for database operations
];
