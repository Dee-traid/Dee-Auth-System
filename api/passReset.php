<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Origin: *');

	require_once '../models/db.php';
	require_once '../models/user.php';

	if($_SERVER['REQUEST_METHOD'] === "POST"){
		User::passwordReset();
	}else{
		http_response_code(405);
		echo json_encode([
			"status" => "error",
        	"message" => "Server method Error"
		]);
	}

?>