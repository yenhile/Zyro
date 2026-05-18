<?php
class Zyro
{
    public function ketnoi()
    {
        $host = "localhost";
        $username = "admin";
        $password = "adminpassword";
        $database = "zyro_db";

        try 
        {
            $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->setAttribute(PDO::ATTR_TIMEOUT, 5);
            return $pdo;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
        }
    }
}

?>