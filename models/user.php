<?php 

require_once '../models/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
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

	public static function userRegistration(){
		$pdo = DatabaseHelper::getPDOInstance();
		try{
			$json = file_get_contents('php://input');
			$data = json_decode($json, true);

			$id = uniqid('user-');
			$fullName = $data['fullname'] ?? null;
			$email = $data['email'] ?? null;
			$password = $data['password'] ?? null;
			$confirmPassword = $data['confirmPassword'] ?? null;

			if (!$fullName || !$email  || !$password || !$confirmPassword) {
				throw new Exception("All fields are required", 1);			
			}

			if ($password !== $confirmPassword) {
            	throw new Exception("Passwords do not match!");
        	}

	        if (strlen($password) < 8) {
	            throw new Exception("Password must be at least 8 characters long.");
	        }

	        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format.");
            }

	        $stmt = $pdo->prepare("SELECT id FROM user_auth WHERE email = :email");
	        $stmt->bindParam(':email', $email);
	        $stmt->execute();
	        $emailCheck = $stmt->fetch(PDO::FETCH_ASSOC); 
			if ($emailCheck) {
			    throw new Exception("This email is already registered.");
			}

			$hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
			$query = " INSERT INTO user_auth(id,fullname,email,password) VALUES (:id, :fullName, :email, :password)";

			$stmt = $pdo->prepare($query);
			$stmt->bindParam(':id', $id);
			$stmt->bindParam(':fullName', $fullName);
			$stmt->bindParam(':email',  $email);
			$stmt->bindParam(':password', $hashedPassword);
			$stmt->execute();

			echo json_encode([
				"status" => "success",
				"message" => "User registration successfull",
				"userId" => $id
			]);
		}catch(Exception $e){
			http_response_code(400);
			echo json_encode([
				"status" => "error",
				"message" => $e->getMessage()
			]);
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
	        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
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
		try{
			$json = file_get_contents('php://input');
			$data = json_decode($json, true);

			$email = $data['email'];
			$password =$data['password'];

			if(!$email || !$password ){
				throw new Exception("All fields are required");
			}
			$query = " SELECT * FROM user_auth WHERE email = :email LIMIT 1";
			$stmt = $pdo->prepare($query);
			$stmt->bindParam(':email', $email);
			$stmt->execute();

			$user = $stmt->fetch(PDO::FETCH_ASSOC);
			if($user && password_verify($password, $user['password'])){
				session_start();
				$_SESSION['userId'] = $user['id'];
				$_SESSION['userName'] = $user['fullname'];

				echo json_encode([
					"status" => "success",
					"message" => " Login successfull",
					"user" => [
	                    "name" => $user['fullname'],
	                    "email" => $user['email']
                ]

				]);

			}else{
				throw new Exception("Invalid email or password.");
			}
		}catch(Exception $e){
			http_response_code(401);
			echo json_encode([
				"status" => "error",
				"message" => $e->getMessage()
			]);

		}
	}

	public static function  recoverPassword(){
		$pdo = DatabaseHelper::getPDOInstance();
		try {
			$json = file_get_contents('php://input');
			$data = json_decode($json, true);
			$email = $data['email'];

			$query = " SELECT email FROM user_auth WHERE email = :email LIMIT 1";
			$stmt = $pdo->prepare($query);
			$stmt->bindParam(':email', $email);
			$stmt->execute();
			$userEmail = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($userEmail) {
				$token = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);	

					if(self::sendTokenToEmail($email, $token)){
							echo json_encode([
							"status" => "success",
							"message" => "Token generated",
							"token" => $token
						]);
					}			
				}else{
					throw new Exception("Email not found");
					return;
				}
			} catch (Exception $e) {
			echo json_encode([
				"status" => "error",
				"message" => $e->getMessage()
		]);
		}
	}

	public static function passwordReset(){
		$pdo = DatabaseHelper::getPDOInstance();
		try {
			$json = file_get_contents('php://input');
			$data = json_decode($json, true);
			if (!$data) { throw new Exception("Invalid request format."); }

			$email = $data['email'];
			$password = $data['password'];
			$confirmPassword = $data['confirmPassword'];

			if ($password !== $confirmPassword) {
	            echo json_encode([
	            	"status" => "error", 
	            	"message" => "Passwords do not match."
	            ]);
	            return;
	        }

	        $hashedPassword = password_hash($password, PASSWORD_BCRYPT,['cost' =>12]);
			$query = " UPDATE user_auth SET password = :password WHERE email = :email";

			$stmt = $pdo->prepare($query);
			$stmt->bindParam(':password', $hashedPassword);
			$stmt->bindParam(':email', $email);
			$stmt->execute();

			echo json_encode([
				"status" => "success",
				 "message" => "Password updated successfully!"
				]);	
		} catch (Exception $e) {
			http_response_code(400);
			echo json_encode([
				"status" => "error",
				"message" => $e->getMessage()
			]);
		}
		
	}
}

?>