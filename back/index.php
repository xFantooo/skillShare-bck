<?php
require_once './config/Database.php';
require_once './routes.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->connect();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

handleRequest($db, $uri, $method);
