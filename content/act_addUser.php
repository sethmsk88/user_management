<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/user_management/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/user_management/includes/globals.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/shared/db_connect.php';

// Insert new user record
try {
	// Make sure the email does not already exist in the user table (perform a case insensitive compare)
	$stmt = $conn->prepare("
		select *
		from user_management.users
		where upper(Email) = upper(?)
	");
	$stmt->bind_param("s", $_POST['addUser-email']);
	$stmt->execute();
	$result = $stmt->get_result();
	if ($result->num_rows > 0) {
		throw new Exception("User with email address '{$_POST['addUser-email']}' already exists");
	}

	if (!$stmt = $conn->prepare("
		insert into user_management.users (Email, FirstName, LastName, Password)
		values (?,?,?,?)
	")) {
		throw new Exception('Prepare statement failed: (' . $stmt->errno . ') ' . $stmt->error);
	}
	if (!$stmt->bind_param("ssss", $_POST['addUser-email'], $_POST['addUser-firstName'], $_POST['addUser-lastName'], $_POST['addUser-password'])) {
		throw new Exception('Binding parameters failed: (' . $stmt->errno . ') ' . $stmt->error);
	}
	if (!$stmt->execute()) {
		throw new Exception('Execute failed: (' . $stmt->errno . ') ' . $stmt->error);
	}
} catch (Exception $e) {
	header("Location: ../index.php?page=users&error=Error adding user: {$e->getMessage()}");
	exit;
}

header("Location: ../index.php?page=users&success=User added: {$_POST['addUser-email']}");
?>
