$(document).ready(function() {
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
					$.each(response.errors, function(i, message) {
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

	// Delete user handler
	$('#confirmDelete-dialog button.submit-btn').click(function (e) {
		var userId = $('#confirmDelete-dialog').attr('data-userid');

		// Delete user
		$.ajax({
			type: 'post',
			url: './content/act_delUser.php',
			data: {'userId' : userId},
			success: function(response) {
				if (response == 1) {
					window.location.reload(); // refresh page
				} else {
					$messageBox = $('div#message-box');
					$messageBox.hide();
					$messageBox.html('Error deleting user. Contact administrator.');
					$messageBox.slideDown();
				}
			}
		});
	});

	// Submit handler for Add User Dialog
	$('#addUser-dialog button.submit-btn').click(function (e) {		
		$form = $('#addUser-form');
		var errors = [];
		var email = $('#addUser-email').val().trim();
		var password = $('#addUser-password').val().trim();
		var confirmPassword = $('#addUser-confirmPassword').val().trim();

		// Validate email address format
		if (!_validateEmail(email)) {
			errors.push("Invalid email format");
		}

		// Password minimum length
		if (password.length < 8) {
			errors.push('Password must be at least 8 characters in length');
		}

		// Confirm that passwords match
		if (password != confirmPassword) {
			errors.push('"Password" and "Confirm Password" must match');
		}

		// Output error messages to the message-box if there are any
		var messageBoxHtml = "";
		if (errors.length > 0) {
			$.each(errors, function(i, message) {
				messageBoxHtml += '<div class="text-danger">'+ message +'</div>';
			});

			// Hide message box
			$messageBox = $('div#message-box');
			$messageBox.hide();

			// Insert messages into message box
			$messageBox.html(messageBoxHtml);

			// Show and animate the message box
			$messageBox.slideDown();

			// return false;
		} else {
			// hash password
			var hashedPassword = hex_sha512(password);
			$('#addUser-password').val(hashedPassword);

			// Clear confirmPassword field
			$('#addUser-confirmPassword').val('');

			// Submit form
			$form.submit();
		}
	});


	var getUrlParameter = function getUrlParameter(sParam) {
	    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
	        sURLVariables = sPageURL.split('&'),
	        sParameterName,
	        i;

	    for (i = 0; i < sURLVariables.length; i++) {
	        sParameterName = sURLVariables[i].split('=');

	        if (sParameterName[0] === sParam) {
	            return sParameterName[1] === undefined ? true : sParameterName[1];
	        }
	    }
	};
	// If GET variables exist
	var successMsg = getUrlParameter("success");
	var errorMsg = getUrlParameter("error");

	// Hide message box
	$messageBox = $('div#message-box');
	$messageBox.hide();

	if (successMsg != undefined) {
		$messageBox.html('<div class="text-success">' + successMsg + '</div>');
		$messageBox.slideDown();
	} else if (errorMsg != undefined) {
		$messageBox.html('<div class="text-danger">' + errorMsg + '</div>');
		$messageBox.slideDown();
	}
});
