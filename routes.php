<?php
require_once './controllers/UserController.php';

function handleRequest($db, $uri, $method) {
    $segments = explode('/', trim($uri, '/'));

    if ($segments[0] === 'users') {
        $controller = new UserController($db);

        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($controller->create($data));
        } elseif ($method === 'GET') {
            echo json_encode($controller->list());
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Route inconnue']);
    }
}
