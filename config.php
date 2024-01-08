<?php

$host = 'users.iee.ihu.gr';
$db = 'navmaxia';
$user = 'it185404';
$pass = '';
$charset = 'utf8mb4';

// Check if the host is 'users.iee.ihu.gr'
if (gethostname() == 'users.iee.ihu.gr') {
    $pdo = new PDO(
        "mysql:host=" . $host . ";dbname=" . $db . ";charset=" . $charset,
        $user,
        $pass,
        [
            // Use a Unix socket for connection
            PDO::MYSQL_ATTR_UNIX_SOCKET => '/home/student/it/2018/it185404/mysql/run/mysql.sock',
        ]
    );
} else {
    // For other hosts, use a traditional connection with a password
    $pdo = new PDO(
        "mysql:host=" . $host . ";dbname=" . $db . ";charset=" . $charset,
        $user,
        $pass
    );
}

return [
    'host' => $host,
    'db' => $db,
    'user' => $user,
    'pass' => $pass,
    'charset' => $charset,
    'pdo' => $pdo, // Return the PDO object for database operations
];
