<?php
// Get all apps
$stmt = $conn->prepare("
	select *
	from user_management.apps
");
$stmt->execute();
$result = $stmt->get_result();
$apps = [];
while ($row = $result->fetch_assoc()) {
	$apps[] = $row;
}

// Get all users and apps for which they have permissions
$stmt = $conn->prepare("
	select u.UserId, u.Email, concat(u.FirstName, ' ', u.LastName) Name,
		a.AppId, a.Name AppName, a.AppDir
	from user_management.users u
	left join user_management.apps_users au on au.UserId = u.UserId
	left join user_management.apps a on a.AppId = au.AppId
	order by u.FirstName
");
$stmt->execute();
$result = $stmt->get_result();
$users = [];
while ($row = $result->fetch_assoc()) {
	$users[] = $row;
}

/*$sel_sections = "
SELECT id, name
FROM " . TABLE_SECTION . "
WHERE srid = ?
";
$stmt = $conn->prepare($sel_sections);
$stmt->bind_param("i", $_GET['id']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($sid, $sectionName);
*/
?>

<div class="container">
	<div class="row">
		<button class="btn btn-default">Add New User</button>
	</div>

	<div class="row">
		<table class="table">
			<thead>
				<tr>
					<th>User</th>
					<th>Email</th>
					<th><!-- Intentionally left blank --></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($users as $user) { /* Begin users loop */ ?>
				<tr>
					<td><?= $user['Name'] ?></td>
					<td><?= $user['Email'] ?></td>
					<td></td>
				</tr>
				<?php } /* End users loop */ ?>
			</tbody>
		</table>
	</div>
</div>
