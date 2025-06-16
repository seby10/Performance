<?php
class Conexion
{
    public static function getConnection()
    {
        // Database connection configuration
        define('DB_HOST', 'localhost');
        define('DB_USER', 'root');
        define('DB_PASS', '');
        define('DB_NAME', 'master_detail');

        //:: -> significa que voy a llamar a un metodo estatico de ese objeto
        $opciones = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'];
        try {
            $conexion = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, $opciones);
            return $conexion;
        } catch (PDOException $e) {
            die("Eror en la conexion: " . $e->getMessage());
        }
    }
}
