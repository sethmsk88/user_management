<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/shared/db_connect.php';

// Get permissions for the given user
$stmt = $conn->prepare("
	select u.UserId, a.AppId, au.AccessLevel, concat(u.FirstName, ' ', u.LastName) FullName
	from user_management.users u
	left join user_management.apps_users au on au.UserId = u.UserId
	left join user_management.apps a on a.AppId = au.AppId
	where u.UserId = ?
	order by a.Name
");
$stmt->bind_param("i", $_POST['userId']);
$stmt->execute();
$result = $stmt->get_result();

$i = 0;
$appPermissions = [];
while ($row = $result->fetch_assoc()) {
	if ($i++ === 0)
		$userFullName = $row['FullName'];

	$appPermissions[] = $row;
}

$jsonDataArray["UserFullName"] = $userFullName;
$jsonDataArray["AppPermissions"] = $appPermissions;
echo json_encode($jsonDataArray);
?>
