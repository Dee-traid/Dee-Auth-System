<?php


require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

class DatabaseHelper{
	private static  $pdo = null;

	public static function getPDOInstance(){
		if(self::$pdo !== null){
			return self::$pdo;
		}
			$host = $_ENV['DB_HOST'];
	        $port = $_ENV['DB_PORT'];
	        $database = $_ENV['DB_NAME'];
	        $user = $_ENV['DB_USER'];
	        $password = $_ENV['DB_PASS'];

			$dsn = "pgsql:host=$host;port=$port;dbname=$database";

			try{
				self::$pdo = new PDO($dsn,$user,$password);
				self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

				return self::$pdo;

			}catch(PDOException $e){
				http_response_code(500);
	            header('Content-Type: application/json');
	            die(json_encode([
	                "status" => "error", 
	                "message" => "Database connection failed " 
	            ]));
			}
	}
		
	
}




?>