<?php
	require_once("./includes/globals.php");

    // Set current page variable
    if (isset($_GET['page']))
        define("APP_CURRENTPAGE", $_GET['page']);
    else
        define("APP_CURRENTPAGE", "");


	require_once $_SERVER['DOCUMENT_ROOT'] . '/apps/shared/dbInfo.php';
    require_once "./includes/functions.php";

    // Open connection to Database
    require_once $_SERVER['DOCUMENT_ROOT'] . '/apps/shared/db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= APP_NAME ?></title>

    <!-- Linked stylesheets -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
    <link href="../css/navbar-custom1.css" rel="stylesheet">
    <link href="../css/master.css" rel="stylesheet">
    <link href="./css/main.css" rel="stylesheet">

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script src="/apps/shared/api/tinymce/js/tinymce/tinymce.min.js"></script>

    <!-- Included Scripts -->
    <script src="./js/main.js"></script>
    <script src="./js/login.js"></script>
    <script src="/js/sha512.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    </head>
    <body>
        <?php
            include_once($_SERVER['DOCUMENT_ROOT'] . "\apps\shared\analyticstracking.php");

            // Include FAMU logo header
            include "../templates/header_3.php";

            // Start session or regenerate session id
            sec_session_start();

            // Check to see if User is logged in
            $loggedIn = login_check($conn);
        ?>

        <!-- Nav Bar -->
        <nav
            id="pageNavBar"
            class="navbar navbar-default navbar-custom1 navbar-static-top"
            role="navigation">

            <div class="container">
                <div class="navbar-header">
                    <button
                        type="button"
                        class="navbar-toggle"
                        data-toggle="collapse"
                        data-target="#navbarCollapse">

                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="?page=<?= APP_HOMEPAGE ?>"><?= APP_NAME ?></a>
                </div>

                <div id="navbarCollapse" class="collapse navbar-collapse">
                    <!-- Nav Links -->
                    <ul class="nav navbar-nav">
                        
                        <?php if ($loggedIn) { ?>
                        <li id="admin-link">
                            <a id="navLink-admin" href="./?page=admin">Admin</a>
                        </li>
                        <?php } ?>
                    </ul>

                    <ul class="nav navbar-nav navbar-right">
                        <?php if ($loggedIn) { ?>
                        <li class="dropdown" style="cursor:pointer;">
                            <a href="#" data-toggle="dropdown" class="dropdown-toggle"><span class="glyphicon glyphicon-user" style="margin-right:8px;"></span><?= $_SESSION['firstName'] ?> <span class="glyphicon glyphicon-triangle-bottom" style="margin-left:4px;"></span></a>
                            <ul class="dropdown-menu">
                                <!-- <li>
                                    <a id="settings-link" href="?page=settings">Settings</a>
                                </li> -->
                                <li>
                                    <a id="logout-link" href="./content/act_logout.php"> Log out</a>
                                </li>
                            </ul>
                        </li>
                        <?php } else { ?>
                        <li>
                            <div class="dropdown">
                                <a href="#" data-toggle="dropdown" class="dropdown-toggle">Log in</a>
                                <ul class="dropdown-menu" style="padding:0px;">
                                    <li>
                                        <?php include_once './includes/inc_login_form.php'; ?>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </nav>


        <?php
            // If a page variable exists, include the page
        	if (isset($_GET["page"])){
        		$filePath = './content/' . $_GET["page"] . '.php';
        	}
        	else{
        		$filePath = './content/' . APP_HOMEPAGE . '.php';
        	}

        	if (file_exists($filePath)){
        		include $filePath;
        	}
        	else{
        		echo '<h2>404 Error</h2>Page does not exist';
        	}
        ?>

        <!-- Footer -->
        <?php include "../templates/footer_1.php"; ?>

    </body>
</html>
