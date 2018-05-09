<?php
/**
 * @return PDO
 */
function createPdo() {
    $host = getenv('DB_HOST', '');
    $userName = getenv('DB_USER_NAME', '');
    $password = getenv('DB_PASSWORD', '');
    $dbName = getenv('DB_NAME', '');

    return new PDO("mysql:dbname=" . $dbName. ";host=" . $host, $userName, $password);
}
