<?php

declare(strict_types=1);

session_start();

spl_autoload_register(function (string $class) {
    require_once ('classes/class_' . $class . '.php');
});

set_error_handler(array('ErrorHandler', 'noticeHandler'), E_ALL);

$config = new Config();
$auth = new Auth();
$template = new Template();
$handler = new Handler();

$template->setTemplate($config->getPageTemplateName('login'), $config->getTemplatesLocation());
$template->setVariable($config->getAllLabels());
$template->setVariable(['title' => 'Login page']);
$template->setVariable(['error_message' => '']);


// Logout user
if ((isset($_GET['logout'])) && ($_GET['logout'] == 'go')) {
	$db = Database::getInstance();
	$cookie = $db->executeQuery("UPDATE `user` SET `us_cookies`=:cookie WHERE `us_id`=:user_id", 
								array(array(':cookie', '', 'string'), array(':user_id', $_SESSION['logged']['us_id'], 'integer')));
    $auth->logoutUser();
	header("Location: index.php");
	
	$template->setTemplate($config->getPageTemplateName('login'), $config->getTemplatesLocation());
	$template->setVariable($config->getAllLabels());
	$template->setVariable(['title' => 'Login page']);
	$template->setVariable(['error_message' => '']);
}

// Login with cookies
if (!isset($_SESSION['logged']) && isset($_COOKIE['remember'])) {
	$user = $auth->loginWithCookies();
	if ($user === Auth::COOKIE_LOGIN_OK) {
		$user = $auth->getUserDataWithCookie($_COOKIE['remember']);
		$_SESSION['logged'] =& $user;
	}
}

// If user is already logged in
if (isset($_SESSION['logged'])) {
	
	$template->setTemplate($config->getPageTemplateName('main'), $config->getTemplatesLocation());
	$template->setVariable(['title' => 'Main page']);
	$template->setVariable(['user_name' => $_SESSION['logged']['us_login']]);
	$template->setVariable(['proxy_message' => '']);
	$template->setVariable(['upload_message' => '']);
	$template->setVariable(['table_message' => '']);
	$handler->setUserFilesTable(	$template, 
									$config->getTemplatesLocation() . '/user_files_table_row.tpl', 
									$_SESSION['logged'], 
									$config->getFoldersLocation());
	$handler->setAllowedExtensionsAndUsage(		$template, 
												$config->getTemplatesLocation() . '/user_data_allowed_extension.tpl', 
												$_SESSION['logged']);
	
} // If user is not logged in
else {
    if (isset($_POST['login']) && (isset($_POST['password']))) {		
        $user = $auth->checkUserCredentialsAndFolder($_POST['login'], sha1($_POST['password']), $config->getFoldersLocation());
		
		if ($user === Auth::CREDENTIALS_RESULT_EMPTY) {
			$template->setVariable(['error_message' => $config->getMessageById('CREDENTIALS_RESULT_EMPTY')]);
		} elseif ($user === Auth::CREDENTIALS_RESULT_BAD_LOGIN_OR_PASSWORD) {
			$template->setVariable(['error_message' => $config->getMessageById('CREDENTIALS_RESULT_BAD_LOGIN_OR_PASSWORD')]);
		} elseif ($user === Auth::CREDENTIALS_RESULT_FOLDER_IS_MISSING) {
			$template->setVariable(['error_message' => $config->getMessageById('CREDENTIALS_RESULT_FOLDER_IS_MISSING')]);
		} else {
			$user = $auth->getUserData($_POST['login'], sha1($_POST['password']));
			if(isset($_POST['remember'])) {
				$cookie_hash = sha1($_POST['login'] . time());
				setcookie('remember', $cookie_hash, time() + 1209600);
				$db = Database::getInstance();
				$cookie = $db->executeQuery("UPDATE `user` SET `us_cookies`=:cookie WHERE `us_id`=:user_id", 
											array(array(':cookie', $cookie_hash, 'string'), array(':user_id', $user['us_id'], 'integer')));
			}
			$_SESSION['logged'] =& $user;
			
			$template->setTemplate($config->getPageTemplateName('main'), $config->getTemplatesLocation());
			$template->setVariable(['title' => 'Main page']);
			$template->setVariable(['user_name' => $_SESSION['logged']['us_login']]);
			$template->setVariable(['proxy_message' => '']);
			$template->setVariable(['upload_message' => '']);
			$template->setVariable(['table_message' => '']);
			$handler->setUserFilesTable(	$template, 
											$config->getTemplatesLocation() . '/user_files_table_row.tpl', 
											$_SESSION['logged'], 
											$config->getFoldersLocation());
			$handler->setAllowedExtensionsAndUsage(		$template, 
														$config->getTemplatesLocation() . '/user_data_allowed_extension.tpl', 
														$_SESSION['logged']);
		}
	}
		
}

$handler->setStatistics($template);
echo $template->getTemplate();