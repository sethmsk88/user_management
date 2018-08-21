$(document).ready(function() {
	// Click handler for Edit App Permissions buttons
	/*$('.edit-app-permissions-btn').click(function() {
		// show dialog to edit the user's app permissions
		$('#editAppPermissions-dialog').dialog();

	});

	// Click handler for Delete User buttons
	$('.delete-user-btn').click(function() {
		console.log('delete user id: ' + $(this).attr('data-userid'));

	});*/

	// When the Edit App Permissions Dialog is toggled ON, populate the app permissions table
	$('#editAppPermissions-dialog').on('show.bs.modal', function (e) {
		
		$clickedBtn = $(e.relatedTarget);
		$userId = $clickedBtn.attr('data-userid');

		// Set the userid of the clicked button as an attribute of the Edit Permissions dialog
		$(this).attr('data-userid', $userId);

		// Get the app permissions for user id
		$.ajax({
			type: 'post',
			url: './content/act_getPermissions.php',
			data: {
				'userId': $userId
			},
			dataType: 'json', // data type for response
			success: function(response) {
				// Set the user's name in the Edit Permissions dialog
				$('#editAppPermissions-dialog').find('#user-fullname').text(response.UserFullName);

				// set the select box values for each app
				// iterate through response JSON object
				// if an AppId has an AccessLevel that is not null, set the select box to the correct Access Level
				$.each(response.AppPermissions, function(idx, el) {
					$('select.app-permissions[data-appid='+ el.AppId +']').val(el.AccessLevel);
				});
			}
		});
	});

	// When the Edit App Permissions Dialog is toggled OFF, clear the app permissions table
	$('#editAppPermissions-dialog').on('hidden.bs.modal', function (e) {
		// Clear the userid and user's full name from the Edit Permissions dialog
		$(this).attr('data-userid', '');
		$(this).find('#user-fullname').text('');

		$('select.app-permissions').val("-1"); // reset select box
	});

	// Submit handler for Edit App Permissions Dialog
	$('#editAppPermissions-dialog button.submit-btn').click(function (e) {
		var userId = $('#editAppPermissions-dialog').attr('data-userid');
		var userObj = {'userid' : userId};
		var appPermissions = [];

		// Store selections in a JSON object
		$('#editAppPermissions-dialog select').each(function () {
			appPermissions.push({
				'appid' : $(this).attr('data-appid'),
				'permissionsLevel' : $(this).val()
			});
		});

		// create JSON object to hold all app and permissions selections
		var postDataObj = {};
		postDataObj['user'] = userObj;
		postDataObj['app-permissions'] = appPermissions;
		var postDataJSON = JSON.stringify(postDataObj);

		// Get the app permissions for user id
		$.ajax({
			type: 'post',
			url: './content/act_updatePermissions.php',
			data: postDataJSON,
			contentType: "application/json",
			dataType: 'json', // data type for response
			success: function(response) {

				// Output error messages to the message-box if there are any
				var messageBoxHtml = "";
				if (response.errors.length > 0) {
					response.errors.forEach(function(message) {
						messageBoxHtml += '<div class="text-danger">'+ message +'</div>';
					});
				}

				// Output success messages to message-box
				messageBoxHtml += '<div class="text-success">App permissions updated</div>';

				// Hide message box
				$messageBox = $('div#message-box');
				$messageBox.hide();

				// Insert messages into message box
				$messageBox.html(messageBoxHtml);

				// Show and animate the message box
				$messageBox.slideDown();
			}
		});
	});

	$('#confirmDelete-dialog').on('show.bs.modal', function (e) {
		$clickedBtn = $(e.relatedTarget);
		$userId = $clickedBtn.attr('data-userid');

		// Set the userid of the clicked button as an attribute of the Confirm Delete dialog
		$(this).attr('data-userid', $userId);
		
		$userName = $(e.relatedTarget).attr('data-username');
		$(this).find('.modal-title').text('Delete ' + $userName);
	});

	// Form confirm (yes/ok) handler, submits form
	/*$('#confirmDelete').find('.modal-footer #confirm').on('click', function(){
		
		// Get fileID from buttonID
		var id_parts = $buttonID.split('-');
		var link_id = id_parts[1];
		
		$.ajax({
			url: './content/act_appendix.php',
			type: 'post',
			data: {
				'actionType': 1, // delete reference action type
				'linkID': link_id
			},
			success: function(response) {
				location.reload();
			}
		});
	});*/
});
