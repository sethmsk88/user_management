<?php
function sec_session_start() {
	$session_name = 'sec_session_id'; // Set a custom session name
	$secure = SECURE;

	// This stops JavaScript being able to access the session id
	$httponly = true;

	/* Forces sessions to only use cookies */
	if (ini_set('session.use_only_cookies', 1) === false) {
		header("Location: ../error.php?err=Could not initiate a safe session (ini_set)");
		exit();
	}

	// Get current cookies params
	$cookieParams = session_get_cookie_params();
	session_set_cookie_params($cookieParams["lifetime"],
		$cookieParams["path"],
		$cookieParams["domain"],
		$secure,
		$httponly);

	// Set the session name to the one set above
	session_name($session_name);

	// Start the PHP session
	session_start();

	// Regenerate the session, delete the old one
	session_regenerate_id(true);
}

function login($email, $password, $conn) {

	sec_session_start();

	/* Get user record with matching email */
	$sel_user_sql = "
		SELECT id, password, firstName, lastName
		FROM secure_login.users
		WHERE email = ?
		LIMIT 1";

	if ($stmt = $conn->prepare($sel_user_sql)) {
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$stmt->store_result();

		// Get variables from result
		$stmt->bind_result($user_id, $db_password, $firstName, $lastName);
		$stmt->fetch();

		// If email address exists in users table
		if ($stmt->num_rows == 1) {

			/*
				If the user exists we check if the account is locked
				from too many login attempts.
			*/
			if (checkbrute($user_id, $conn) == true) {
				// Account is locked
				// Send email to user saying their account is locked
				return false;
			}
			else {
				/*
					Check if the password in the DB matches the
					password the user submitted
				*/

				if ($db_password == $password) {

					// Password is correct
					// Get the user-agent string of the user
					$user_browser = $_SERVER['HTTP_USER_AGENT'];

					$user_id = preg_replace("/[^0-9]+/", "", $user_id);
                    $_SESSION['user_id'] = $user_id;
                    
                    // XSS protection as we might print these values
                    $firstName = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $firstName);
                    $lastName = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $lastName);

					$_SESSION['firstName'] = $firstName;
					$_SESSION['lastName'] = $lastName;
					$_SESSION['login_string'] = hash('sha512', $password . $user_browser);

					// Login successful
					return true;
				}
				else {

					// Password is not correct
					// We record this attempt in the database
					$now = time();

					$ins_login_attempt_sql = "
						INSERT INTO secure_login.login_attempts(user_id, time)
						VALUES ('$user_id', '$now')
					";
					$conn->query($ins_login_attempt_sql);

					return false;
				}
			}
		}
		else {

			// No user exists
			return false;
		}
	}
}

function checkbrute($user_id, $conn) {
	$now = time();

	// All login attempts are counted from the past 2 hours
	$valid_attempts = $now - (2 * 60 * 60);

	$sel_login_times_sql = "
		SELECT time
		FROM secure_login.login_attempts
		WHERE user_id = ? AND
			time > '$valid_attempts'
	";

	if ($stmt = $conn->prepare($sel_login_times_sql)) {
		$stmt->bind_param('i', $user_id);
		$stmt->execute();
		$stmt->store_result();

		// If there are more than 5 failed logins
		if ($stmt->num_rows > 5) {
			return true;
		}
		else {
			return false;
		}
	}
}

function login_check($conn) {
	
	$loggedIn = false; // Default

	// Check if all session variables are set
	if (isset($_SESSION['user_id'],
			$_SESSION['firstName'],
			$_SESSION['lastName'],
			$_SESSION['login_string'])) {

		$user_id = $_SESSION['user_id'];
		$firstName = $_SESSION['firstName'];
		$lastName = $_SESSION['lastName'];
		$login_string = $_SESSION['login_string'];

		// Get the user-agent string of the user
		$user_browser = $_SERVER['HTTP_USER_AGENT'];

		$sel_user_pw_sql = "
			SELECT password
			FROM secure_login.users
			WHERE id = ?
			LIMIT 1
		";

		if ($stmt = $conn->prepare($sel_user_pw_sql)) {
			$stmt->bind_param('i', $user_id);
			$stmt->execute();
			$stmt->store_result();

			if ($stmt->num_rows == 1) {
				// If the user exists get variables from result
				$stmt->bind_result($password);
				$stmt->fetch();
				$login_check = hash('sha512', $password . $user_browser);

				if ($login_check == $login_string) {
					// Logged in
					$loggedIn = true;
				}
			}
		}
	}
	return $loggedIn;
}

function esc_url($url) {
	if ('' == $url) {
		return $url;
	}

	$url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);

	$strip = array('%0d', '%0a', '%0D', '%0A');
	$url = (string) $url;

	$count = 1;
	while ($count) {
		$url = str_replace($strip, '', $url, $count);
	}

	$url = str_replace(';//', '://', $url);
	$url = htmlentities($url);
	$url = str_replace('&amp;', '&#038;', $url);
    $url = str_replace("'", '&#039;', $url);

    if ($url[0] !== '/') {
    	// We're only interested in relative links from $_SERVER['PHP_SELF']
    	return '';
    }
    else {
    	return $url;
    }
}

// redirect if not logged in
function require_login($loggedIn) {
	if (!$loggedIn) {
		echo '<script>location.href="?page=' . APP_HOMEPAGE . '"</script>';
		exit;
	}
}

// Increment the number on the end of the filename
function increment_fileNumber($fileName)
{		
	// Split string by '.'
	$fileName_exploded = explode('.', $fileName);

	// Pop the file extension off the end
	$extension = array_pop($fileName_exploded);

	// Join array into string using '.' as a separator
	$fileName = implode('.', $fileName_exploded);

	// Split the string by '_'
	$fileName_exploded = explode('_', $fileName);

	// Remove the number that was previously appended to the filename
	$prevNumber = array_pop($fileName_exploded);

	// Add a new number to the filename
	array_push($fileName_exploded, ++$prevNumber);

	// Join array into string using '_' as a separator
	$fileName = implode('_', $fileName_exploded) . '.' . $extension;

	return $fileName;
}

// Return a filename that does not already exist in the uploads directory
function make_unique_filename($fileName, $uploadsDir)
{
	if (file_exists(APP_PATH . $uploadsDir . $fileName)) {
		$fileName_exploded = explode('.', $fileName);
		$extension = array_pop($fileName_exploded);
		array_push($fileName_exploded, '_1'); // Append number to filename
		array_push($fileName_exploded, $extension);
		$fileName = implode('.', $fileName_exploded);

		// Make sure filename is unique
		while (file_exists(APP_PATH . $uploadsDir . $fileName)) {
			$fileName = increment_fileNumber($fileName);
		}
	}
	return $fileName;
}

function delete_file_from_server($fileName, $pathToDir)
{
	$originalDir = getcwd(); // original working directory
	chdir($pathToDir); // change working directory to uploads directory
	unlink($fileName); // delete file from server
	chdir($originalDir); // change working directory to original wd
}
?>
