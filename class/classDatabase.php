<?php
if (file_exists(__DIR__ . '/../config/database.php')) {
    require_once(__DIR__ . '/../config/database.php');
}

class Zyro
{
    protected static $cachedPdo = null;

    public function ketnoi()
    {
        if (self::$cachedPdo !== null) {
            return self::$cachedPdo;
        }

        // Hỗ trợ cấu hình linh hoạt qua config/database.php, biến môi trường hoặc mặc định localhost
        $host = defined('DB_HOST') ? DB_HOST : (getenv('DB_HOST') ? getenv('DB_HOST') : (isset($_SERVER['DB_HOST']) ? $_SERVER['DB_HOST'] : "localhost"));
        $username = defined('DB_USER') ? DB_USER : (getenv('DB_USER') ? getenv('DB_USER') : (isset($_SERVER['DB_USER']) ? $_SERVER['DB_USER'] : "root"));
        $password = defined('DB_PASS') ? DB_PASS : (getenv('DB_PASS') !== false ? getenv('DB_PASS') : (isset($_SERVER['DB_PASS']) ? $_SERVER['DB_PASS'] : ""));
        $database = defined('DB_NAME') ? DB_NAME : (getenv('DB_NAME') ? getenv('DB_NAME') : (isset($_SERVER['DB_NAME']) ? $_SERVER['DB_NAME'] : "zyro_db"));
        $port = defined('DB_PORT') ? DB_PORT : 3306;

        try 
        {
            $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8", $username, $password);
            $pdo->exec("SET NAMES 'utf8'");
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->setAttribute(PDO::ATTR_TIMEOUT, 5);
            self::$cachedPdo = $pdo;
            return self::$cachedPdo;
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

    public function getConnection()
    {
        return $this->ketnoi();
    }
}

class Database extends Zyro {}

?>