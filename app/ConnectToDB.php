<?php

namespace App;

use PDO;
use PDOException;

class ConnectToDB
{
    public function connect()
    {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=friendsapp', '', '');
            return $pdo;
        } catch (PDOException $e) {
            print "Error!" . $e->getMessage() . "<br/>";
            die();
        }
    }
}