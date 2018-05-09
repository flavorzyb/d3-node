<?php
use Dotenv\Dotenv;

require __DIR__.'/vendor/autoload.php';
$config = new Dotenv(__DIR__);
$config->load();
