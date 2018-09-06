<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/user_management/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/user_management/includes/globals.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/shared/db_connect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/shared/login_functions.php';

sec_session_start();

// for return data
$jsonDataArray = [
	"errors" => [],
	"success" => false
];

try {
	// Make sure that it is a POST request.
	if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
	    throw new Exception('Request method must be POST!');
	}
	 
	// Make sure that the content type of the POST request has been set to application/json
	$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
	if(strcasecmp($contentType, 'application/json') != 0){
	    throw new Exception('Content type must be: application/json');
	}
	 
	// Receive the RAW post data.
	$content = trim(file_get_contents("php://input"));
	 
	// Attempt to decode the incoming RAW post data from JSON.
	$jsonData = json_decode($content, true);

	// If json_decode failed, the JSON is invalid.
	if(!is_array($jsonData)){
	    throw new Exception('Received content contained invalid JSON!');
	}
} catch (Exception $e) {
	$jsonDataArray["errors"][] = $e->getMessage();
	echo json_encode($jsonDataArray);
	exit;
}

// Delete all permissions records for this user
try {
	$stmt = $conn->prepare("
		delete from user_management.apps_users
		where UserId = ?
	");
	$stmt->bind_param("i", $jsonData['user']['userid']);
	
	if (!$stmt->execute()) {
		throw new Exception($stmt->error);
	}
} catch (Exception $e) {
	$jsonDataArray["errors"][] = $e->getMessage();
	echo json_encode($jsonDataArray);
	exit;
}

// Insert new permissions records
try {
	if (!$stmt = $conn->prepare("
		insert into user_management.apps_users (AppId, UserId, AccessLevel)
		values (?,?,?)
	")) {
		throw new Exception('Prepare statement failed: (' . $stmt->errno . ') ' . $stmt->error);
	}
	foreach ($jsonData['app-permissions'] as $key => $val) {
		// Only insert records for the apps the user has access to
		if ($val['permissionsLevel'] > -1) {

			// If this appid is the user_management app and we are not allowing
			// the change of user_management app permissions, skip this iteration.
			if ($val['appid'] == $_GLOBALS['APP_ID'] && $allowChangeUserManagement) {
				continue;
			}
			
			if (!$stmt->bind_param("iii", $val['appid'], $jsonData['user']['userid'], $val['permissionsLevel'])) {
				throw new Exception('Binding parameters failed: (' . $stmt->errno . ') ' . $stmt->error);
			}
			if (!$stmt->execute()) {
				throw new Exception('Execute failed: (' . $stmt->errno . ') ' . $stmt->error);
			}
		}
	}
} catch (Exception $e) {
	$jsonDataArray["errors"][] = "ERROR inserting a permissions record: {$e->getMessage()}<br>";
	echo json_encode($jsonDataArray);
	exit;
}

$jsonDataArray["success"] = true;
echo json_encode($jsonDataArray);
?>
