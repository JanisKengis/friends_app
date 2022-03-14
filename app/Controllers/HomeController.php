<?php

namespace App\Controllers;

use App\View;

class HomeController
{
    public function home(): View
    {
        return new View('home', [
            'user' => $_SESSION['name']
        ]);
    }
}