<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/shared/db_connect.php';

// Delete a user
try {
	if (!$stmt = $conn->prepare("
		delete from user_management.users
		where UserId = ?
	")) {
		throw new Exception($stmt);
	} else if (!$stmt->bind_param("i", $_POST['userId'])) {
		throw new Exception($stmt);
	} else if (!$stmt->execute()) {
		throw new Exception($stmt);
	}
} catch (Exception $e) {
	echo 0;
	exit;
}

echo 1;
?>
