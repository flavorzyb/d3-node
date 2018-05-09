<?php
define('DB_HOST', getenv('DB_HOST', ''));
define('DB_USER_NAME', getenv('DB_USER_NAME', ''));
define('DB_PASSWORD', getenv('DB_PASSWORD', ''));
define('DB_NAME', getenv('DB_NAME', ''));

function createPdo() {
    return new PDO("mysql:dbname=" . DB_NAME. ";host=" . DB_HOST, DB_USER_NAME, DB_PASSWORD);
}
