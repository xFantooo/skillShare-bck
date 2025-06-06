<?php

declare(strict_types=1);

namespace App\controllers;

use App\core\attributes\Route;

class HomeController
{
    #[Route('/', 'GET')]
    function homeView()
    {
        echo "Welcome to the Home Page!";
    }
}
