<?php

declare(strict_types=1);

class Auth
{
    const CREDENTIALS_RESULT_OK = 0;
	const CREDENTIALS_RESULT_EMPTY = -1;
    const CREDENTIALS_RESULT_BAD_LOGIN_OR_PASSWORD = -2;
    const CREDENTIALS_RESULT_FOLDER_IS_MISSING = -3;
	const COOKIE_LOGIN_OK = 0;
	const COOKIE_LOGIN_FAILED = -1;
	
	private $db = false;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function checkEnteredData(string $variable): string
    {
        $variable = preg_replace('/[^a-zA-Z0-9_.-]/', '', $variable);
        return $variable;
    }

    public function checkUserCredentialsAndFolder(string $login, string $password, string $folders_location): int
    {
        $login = $this->checkEnteredData($login);
        $password = $this->checkEnteredData($password);
		
		if (empty($login) || empty($password)) {
			return Auth::CREDENTIALS_RESULT_EMPTY;
		}

		$user = $this->db->executeQuery("SELECT * FROM `user` WHERE `us_login`=:us_login AND `us_password`=:us_password", 
											array(array(':us_login', $login, 'string'), array(':us_password', $password, 'string')));
		$user = $user->fetch(PDO::FETCH_ASSOC);

		if (empty($user)) {
			return Auth::CREDENTIALS_RESULT_BAD_LOGIN_OR_PASSWORD;
		} 
		
		$user_home_dir = $folders_location . '/' . $user['us_folder'] . '/';
		
		if (!is_dir($user_home_dir)) {
			return Auth::CREDENTIALS_RESULT_FOLDER_IS_MISSING;
		} 
		
		return Auth::CREDENTIALS_RESULT_OK;
    }
	
	public function loginWithCookies(): int
	{
		$data = $this->db->executeQuery("SELECT * FROM `user` WHERE `us_cookies`=:us_cookies", 
											array(array('us_cookies', $_COOKIE['remember'], 'string')));
		$data = $data->fetch(PDO::FETCH_ASSOC);
		
		if(empty($data)) {
			return Auth::COOKIE_LOGIN_FAILED;
		} else {
			return Auth::COOKIE_LOGIN_OK;
		}
	}
	
	public function getUserDataWithCookie(string $cookie): array
	{
		$user = $this->db->executeQuery("SELECT `us_login`, `us_password` FROM `user` WHERE `us_cookies`=:cookie",
										array(array(':cookie', $cookie, 'string')));
		$user = $user->fetch();
		
		$user_data = $this->getUserData($user['us_login'], $user['us_password']);
		return $user_data;
	}

    public function getUserData(string $login, string $password): array
    {
        $user_data = array();
		
		$data = $this->db->executeQuery("SELECT * FROM `user` WHERE `us_login`=:us_login AND `us_password`=:us_password", 
											array(array(':us_login', $login, 'string'), array(':us_password', $password, 'string')));
		$user_data = $data->fetch(PDO::FETCH_ASSOC);
		
		if (empty($user_data)) {
			return array();
		} 
		
		$data = $this->db->executeQuery("SELECT `frm_name`, `allw_frm_format_max_size` FROM `all_formats`
										   INNER JOIN `allowed_format` ON `all_formats`.`frm_id` = `allowed_format`.`allw_frm_format_id`
												INNER JOIN `user` ON `allowed_format`.`allw_frm_user_id` = `user`.`us_id`
													WHERE `user`.`us_id` = (SELECT `us_id` FROM `user` WHERE `us_login`=:us_login)", 
														array(array(':us_login', $login, 'string')));
        $allowedExtensions = $data->fetchAll(PDO::FETCH_KEY_PAIR);
		
		$user_data['allowed_extensions'] = $allowedExtensions;
		
		$data = $this->db->executeQuery("SELECT `fl_spu_total_size` FROM `file_size_per_user`
                                                INNER JOIN `user` ON `file_size_per_user`.`fl_spu_user_id` = `user`.`us_id`
                                                    WHERE `user`.`us_id` = (SELECT `us_id` FROM `user` WHERE `us_login`=:us_login)",
														array(array(':us_login', $login, 'string')));
		$userUploads = $data->fetch(PDO::FETCH_ASSOC);
		
		$user_data['uploads_total_size'] = $userUploads['fl_spu_total_size'];
		
        return $user_data;
    }
	
	public function logoutUser(): void
    {
		
        setcookie('remember', '---', -1);
		unset($_SESSION['logged']);
		
    }
}