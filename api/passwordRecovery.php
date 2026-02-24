<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

require_once '../models/db.php';
require_once '../models/user.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
	User::recoverPassword();
}else{
	http_response_code(405);
	echo json_encode([
		"message" => " Server method Error"
	]);
}

?>