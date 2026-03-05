<?php 

require_once '../models/db.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../api/csrf_helper.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

class User{
	public string $id;
	public string $fullName;
	public string $email;
	public string $password;

	public function __construct(
		string $id,
		string $fullName,
		string $email,
		string $password
	){
		$this->id = $id;
		$this->fullName = $fullName;
		$this->email = $email;
		$this->password = $password;
	}
	public function getID(){ return $this->id; }
	public function getFullName(){ return $this->fullName;}
	public function getEmail(){ return $this->email;}
	public function getPassword(){ return $this->password;}


	public static function mapToUserRow(array $row){
		$id = $row['id'];
		$fullName = $row['fullname'];
		$email = $row['email'];
		$password = $row['password'];

		return new User($id, $fullName, $email, $password);
	}

	private static function sendJSON($status, $message, $code = 200, $extra = []) {
        http_response_code($code);
        $response = array_merge([
            "status" => $status,
            "message" => $message
        ], $extra);
        echo json_encode($response);
        exit();
    }

	public static function userRegistration(){
		$pdo = DatabaseHelper::getPDOInstance();
		try{
			$json = file_get_contents('php://input');
			$data = json_decode($json, true);

			$id = uniqid('user-');
			$fullName = $data['fullname'] ?? null;
			$email = filter_var( $data['email'] ?? '' ,  FILTER_SANITIZE_EMAIL);
			$password = $data['password'] ?? null;
			$confirmPassword = $data['confirmPassword'] ?? null;

			$fields = ['fullname', 'email', 'password', 'confirmPassword'];
            foreach ($fields as $field) {
                if (empty($data[$field])) {
                    self::sendJSON("error", "The field '$field' is required", 400);
                }
            }

			if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $data['password'])) {
                self::sendJSON("error", "Password must be at least 8 characters and contain both letters and numbers.", 400);
            }

            if ($data['password'] !== $data['confirmPassword']) {
                self::sendJSON("error", "Passwords do not match.", 400);
            }

	        $stmt = $pdo->prepare("SELECT id FROM user_auth WHERE email = :email");
	        $stmt->bindParam(':email', $email);
	        $stmt->execute();
	        $emailCheck = $stmt->fetch(PDO::FETCH_ASSOC); 
			if ($emailCheck) {
			    self::sendJSON("error", "This email is already registered.", 409);
			}

			$hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
			$query = " INSERT INTO user_auth(id,fullname,email,password) VALUES (:id, :fullName, :email, :password)";

			$stmt = $pdo->prepare($query);
			$stmt->bindParam(':id', $id);
			$stmt->bindParam(':fullName', $fullName);
			$stmt->bindParam(':email',  $email);
			$stmt->bindParam(':password', $hashedPassword);
			$stmt->execute();

			self::sendJSON("success", "Registration successful!", 201, ["userId" => $id]);

		}catch(Exception $e){
			self::sendJSON("error", "Internal Server Error: " . $e->getMessage(), 500);
		}

	}


	private static function sendTokenToEmail($email, $token){
		$mail = new PHPMailer(true);

		try{
			$mail->isSMTP();
			$mail->Host = $_ENV['MAIL_HOST'];
	        $mail->SMTPAuth = true;
	        $mail->Username = $_ENV['MAIL_USER'];
	        $mail->Password = $_ENV['MAIL_PASS'];
	       $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
	        $mail->Port = $_ENV['MAIL_PORT'];

	        $mail->setFrom($_ENV['MAIL_USER'], $_ENV['MAIL_FROM_NAME']);
        	$mail->addAddress($email);

        	$mail->isHTML(true);
	        $mail->Subject = 'Your Verification Token';
	        $mail->Body    = "Your security token is: <b style='font-size:20px; '>$token</b><br>This token expires in 5 minutes.";

	        return $mail->send();

		}catch(Exception $e){
			echo $e->getMessage();
			return false;
		}
	}

	public static function userLogin(){
		$pdo = DatabaseHelper::getPDOInstance();
		header("Content-Type: application/json");
		try{
			$json = file_get_contents('php://input');
			$data = json_decode($json, true);

			if (!isset($data['csrf_token'] )|| !verifyCsrfToken($data['csrf_token'])) {
				self::sendJSON("error", "Security session expired. Please refresh.", 403);
			}

			$email = filter_var( $data['email'] ?? '' ,  FILTER_SANITIZE_EMAIL);
			$password =$data['password'];

			if (empty($email) || empty($password)) {
                self::sendJSON("error", "Please enter both email and password.", 400);
            }

			$rateCheckQuery = "SELECT  COUNT(*) FROM login_attempts WHERE  email = :email AND attempted_at > NOW() - INTERVAL '15 minutes'";
			$rateCheck = $pdo->prepare($rateCheckQuery);
			$rateCheck->bindParam(':email', $email);
			$rateCheck->execute();

			if($rateCheck->fetchColumn() >= 5){
					self::sendJSON("error", "Too many attempts. Locked for 15 minutes.", 429);
			}

			$query = " SELECT * FROM user_auth WHERE email = :email LIMIT 1";
			$stmt = $pdo->prepare($query);
			$stmt->bindParam(':email', $email);
			$stmt->execute();

			$user = $stmt->fetch(PDO::FETCH_ASSOC);
			if($user && password_verify($password, $user['password'])){
				$rt = $pdo->prepare("DELETE FROM login_attempts WHERE email = :email");
				$rt->bindParam(':email', $email);
				$rt->execute();

				if(session_status() === PHP_SESSION_NONE) session_start();
				$secretKey = $_ENV['JWT_SECRET'];
	            $payload = [
	                "iss" => "Dee-Auth-System",
	                "iat" => time(),
	                "exp" => time() + 3600, // Token expires in 1 hour
	                "uid" => $user['id']
	            ];

	            $jwt = JWT::encode($payload, $secretKey, 'HS256');

					self::sendJSON("success", "Login successful", 200, [
	                "token" => $jwt,
	                "user" => [
	                	"id" => $user['id'],
	                    "name" => $user['fullname'],
	                    "email" => $user['email']
	                ]
            	]);

			}else{
				$ip =  $_SERVER['REMOTE_ADDR'];
				$logStmt = $pdo->prepare("INSERT INTO login_attempts (email, ip_address) VALUES (:email, :ip)");
				$logStmt ->bindParam(':email', $email);
				$logStmt->bindParam(':ip', $ip);
	            $logStmt->execute();
	            self::sendJSON("error", "Invalid credentials.", 401);
			}
		}catch(Exception $e){
			self::sendJSON("error", "Server Error: " . $e->getMessage(), 500);

		}
	}

	public static function  recoverPassword(){
		$pdo = DatabaseHelper::getPDOInstance();
		try {
			$json = file_get_contents('php://input');
			$data = json_decode($json, true);

			$email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
			if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            	self::sendJSON("error", "Please provide a valid email address.", 400);
        	}

			$query = " SELECT email FROM user_auth WHERE email = :email LIMIT 1";
			$stmt = $pdo->prepare($query);
			$stmt->bindParam(':email', $email);
			$stmt->execute();
			$userEmail = $stmt->fetch(PDO::FETCH_ASSOC);

				if ($userEmail) {
					$token = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);	

						if (self::sendTokenToEmail($email, $token)) {
			                self::sendJSON("success", "Verification code sent to your email.", 200, ["token" => $token]);
			            } else {
			                self::sendJSON("error", "Email delivery failed. Try again later.", 500);
			            }
			     }	else{
			     	self::sendJSON("error", "No account found with that email.", 404);
			     }
			} catch (Exception $e) {
				self::sendJSON("error", "Server Error: " . $e->getMessage(), 500);
		}
	}

	public static function passwordReset(){
		$pdo = DatabaseHelper::getPDOInstance();
		try {
			$json = file_get_contents('php://input');
			$data = json_decode($json, true);
			if (!$data) {  
				self::sendJSON("error", "Invalid Format",  500);
			 }

			$email = filter_var( $data['email'] ?? '' ,  FILTER_SANITIZE_EMAIL);
			$password = $data['password'];
			$confirmPassword = $data['confirmPassword'];

			if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
	            self::sendJSON("error", "Password must be 8+ characters with letters and numbers.", 400);
	        }

	        if ($password !== $confirmPassword) {
	            self::sendJSON("error", "Passwords do not match.", 400);
	        }

	        $hashedPassword = password_hash($password, PASSWORD_BCRYPT,['cost' =>12]);
			$query = " UPDATE user_auth SET password = :password WHERE email = :email";

			$stmt = $pdo->prepare($query);
			$stmt->bindParam(':password', $hashedPassword);
			$stmt->bindParam(':email', $email);
			$stmt->execute();

			if ($stmt->rowCount() > 0) {
	            self::sendJSON("success", "Password updated! You can now login.", 200);
	        } else {
	            self::sendJSON("error", "Reset failed. Session may have expired.", 400);
	        }
		} catch (Exception $e) {
			self::sendJSON("error", "Database Error: " . $e->getMessage(), 500);
		}
		
	}
}

?>