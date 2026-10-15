<?php
class Zyro
{
    public function ketnoi()
    {
        // Hỗ trợ cấu hình linh hoạt qua biến môi trường khi deploy lên Production Hosting
        $host = getenv('DB_HOST') ? getenv('DB_HOST') : (isset($_SERVER['DB_HOST']) ? $_SERVER['DB_HOST'] : "localhost");
        $username = getenv('DB_USER') ? getenv('DB_USER') : (isset($_SERVER['DB_USER']) ? $_SERVER['DB_USER'] : "root");
        $password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : (isset($_SERVER['DB_PASS']) ? $_SERVER['DB_PASS'] : "");
        $database = getenv('DB_NAME') ? getenv('DB_NAME') : (isset($_SERVER['DB_NAME']) ? $_SERVER['DB_NAME'] : "zyro_db");

        try 
        {
            $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
            $pdo->exec("SET NAMES 'utf8'");
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->setAttribute(PDO::ATTR_TIMEOUT, 5);
            return $pdo;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
        }
    }

    public function connect()
    {
        return $this->ketnoi();
    }
}

?>