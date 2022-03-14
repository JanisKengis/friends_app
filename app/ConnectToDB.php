<?php

namespace App;

use PDO;
use PDOException;

class ConnectToDB
{
    private static $connection = null;

    public static function connect()
    {
        if(self::$connection === null) {
            try {
                self::$connection = new PDO('mysql:host=localhost;dbname=friendsapp', 'phpadmin', '');
            } catch (PDOException $e) {
                print "Error!" . $e->getMessage() . "<br/>";
                die();
            }
        }
        return self::$connection;
    }
}