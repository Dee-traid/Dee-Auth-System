<?php
 header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once '../models/db.php';
require_once '../models/user.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
	User::userLogin();
}else{
	http_response_code(405);
	echo json_encode([
		"message" => "Invalid request method"
	]);
}

 


?>