<?php

class Config
{
    private static ?PDO $pdo = null;

    public static function getConnexion()
    {
        error_reporting(E_ALL);
ini_set('display_errors', 1);
        if (self::$pdo === null) {

            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "projetweb1";

            try {
                self::$pdo = new PDO(
                    "mysql:host=$servername;dbname=$dbname;charset=utf8",
                    $username,
                    $password
                );

                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                die("Erreur connexion : " . $e->getMessage());
            }
        }

        return self::$pdo;
    }
    
}