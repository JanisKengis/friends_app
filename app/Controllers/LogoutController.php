<?php

namespace App\Controllers;
use App\Redirect;

class LogoutController
{
    public function logout(): Redirect
    {
        session_start();
        session_unset();
        session_destroy();
        return new Redirect('/');
    }
}