<script type="text/javascript" src="<?= $GLOBALS['APP_PATH_URL'] ?>js/content/users.js"></script>

<?php
// Make sure this user has access to this application
if (!$GLOBALS['LOGGED_IN']) {
	echo '<div class="container text-danger h4">Must be logged in to access this application</div>';
	exit;
} else if (is_null($GLOBALS['ACCESS_LEVEL'])) {
	echo '<div class="container text-danger h4">You do not have access to this application</div>';
	exit;
}

// Get all apps
$stmt = $conn->prepare("
	select *
	from user_management.apps
	order by Name
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
		a.AppId, a.Name AppName, a.AppDir, au.AccessLevel
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
?>

<div class="container">
	<div class="row">
		<button class="btn btn-default">Add New User</button>
	</div>

	<div class="row">
		<table class="table table-hover">
			<thead>
				<tr>
					<th>User</th>
					<th>Email</th>
					<th><!-- Intentionally left blank --></th>
				</tr>
			</thead>
			<tbody>
				<?php
					$prevUserId = -1;
					foreach ($users as $user) { /* Begin users loop */
						// If this user has already been printed, skip to next iteration of loop
						if ($user['UserId'] !== $prevUserId)
							$prevUserId = $user['UserId'];
						else
							continue;
				?>
				<tr>
					<td><?= $user['Name'] ?></td>
					<td><?= $user['Email'] ?></td>
					<td>
						<div class="row">
							<div class="col-xs-6 text-center">
								<button class="btn btn-primary btn-xs edit-app-permissions-btn"
									data-userid="<?= $user['UserId'] ?>"
									data-toggle="modal"
									data-target="#editAppPermissions-dialog">
									Edit App Permissions
								</button>
							</div>
							<div class="col-xs-6 text-center">
								<button class="btn btn-danger btn-xs delete-user-btn"
									data-userid="<?= $user['UserId'] ?>"
									data-toggle="modal"
									data-username="<?= $user['Name'] ?>"
									data-target="#confirmDelete-dialog">
									Delete User
								</button>
							</div>
						</div>
					</td>
				</tr>
				<?php } /* End users loop */ ?>
			</tbody>
		</table>
	</div>
</div>


<!---------------------------->
<!--        Dialogs         -->
<!---------------------------->

<!-- Edit App Permissions Dialog -->
<div class="modal fade"
	id="editAppPermissions-dialog"
	role="dialog"
	data-userid=""
	tabindex="-1"
	aria-labelledby=""
	aria-hidden="true">

	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Edit App Permissions</h4>
				<button
					type="button"
					class="close"
					data-dismiss="modal"
					aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				App permissions for <span id="user-fullname" style="font-weight:bold;"></span>.
				<table class="table">
					<thead>
						<tr>
							<th>App Name</th>
							<th>Permissions Level</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($apps as $app) { /* Begin apps loop */ ?> 
						<tr>
							<td><?= $app['Name'] ?></td>
							<td>
								<select class="app-permissions" data-appid="<?= $app['AppId'] ?>">
									<option value="-1" selected="selected">Guest</option>
									<option value="1">Standard</option>
									<option value="0">Admin</option>
								</select>
							</td>
						</tr>
						<?php } /* End apps loop */ ?>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
	        	<button type="button" class="btn btn-default submit-btn" data-dismiss="modal">Submit Changes</button>
	        </div>
		</div>
	</div>
</div>

<!-- Delete Modal Dialog -->
<div
	class="modal fade"
	id="confirmDelete-dialog"
	role="dialog"
	data-userid=""
	aria-labelledby="confirmDeleteLabel"
	aria-hidden="true">

	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button
					type="button"
					class="close"
					data-dismiss="modal"
					aria-hidden="true">
					&times;
				</button>
	        	<h4 class="modal-title"></h4>
			</div>
			<div class="modal-body">
				<p>Are you sure you want to delete this user?</p>
			</div>
			<div class="modal-footer">
				<button
					type="button"
					class="btn btn-default"
					data-dismiss="modal">
					Cancel
				</button>
	        	<button
	        		type="button"
	        		class="btn btn-danger"
	        		id="confirm">
	        		Yes
	        	</button>
			</div>
		</div>
	</div>
</div>

