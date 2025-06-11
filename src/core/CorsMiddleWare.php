<?php

declare(strict_types=1);

namespace App\core;

class CorsMiddleWare
{
    public function handle() {
        header('Access-Control-Allow-Origin: http://localhost:3000');
        // definir les en-têtes CORS
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        // définir les méthodes autorisées
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        // définir le type de contenue par défaut
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Credentials: true');
        //Gérer les requêtes préflight

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
        
    }
}
