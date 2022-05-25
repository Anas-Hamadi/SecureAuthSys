<?php
	session_start();
	$errors = [];


	if(!isset($_POST['name']) || strlen($_POST['name']) > 255 || !preg_match('/^[a-zA-Z- ]+$/', $_POST['name'])) {
		$errors[] = 1;
	}
	if(!isset($_POST['email']) || strlen($_POST['email']) > 255 || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		$errors[] = 2;
	}
	else if(!checkdnsrr(substr($_POST['email'], strpos($_POST['email'], '@') + 1), 'MX')) {
		$errors[] = 3;
	}
	if(!isset($_POST['password']) || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\~?!@#\$%\^&\*])(?=.{8,})/', $_POST['password'])) {
		$errors[] = 4;
	}
	else if(!isset($_POST['confirm-password']) || $_POST['confirm-password'] !== $_POST['password']) {
		$errors[] = 5;
	}



	if(count($errors) === 0) {
		if(isset($_POST['csrf_token']) && validateToken($_POST['csrf_token'])) {
			//Connect to database
			$C = connect();
			if($C) {
					// Updating 
					$hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
					$sql = "UPDATE users SET users.name = :n , email = :e, password =:p where id=:id";
					$q = $C->prepare($sql);
					$q->execute(['n'=>$_POST['name'], 'e'=>$_POST['email'], 'p'=>$hash,'id'=> $_SESSION['userID']]);
					  
					// $id = sqlUpdate($C, 'UPDATE users SET (users.name=?,email=?, password=?, verified=0) WHERE id=?','sssi', $_POST['name'], $_POST['email'], $hash,$_SESSION['userID']);
					$res->free_result();
				}
			else {
				//Failed to connect to database
				$errors[] = 8;
			}
		}
		else {
			//Invalid CSRF Token
			$errors[] = 9;
		}
	}


	echo json_encode($errors);
