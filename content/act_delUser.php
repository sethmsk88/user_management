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
} catch ($e) {
	echo json_encode([
		'error' => 'Failed to delete User: ' . $e->errno . ' - ' . $e->error
	]);
	exit;
}

echo json_encode([
	'success' => true
]);
?>
