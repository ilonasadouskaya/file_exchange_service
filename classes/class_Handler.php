<?php

declare(strict_types=1);

class Handler
{
	// Set app statistics
	public function setStatistics(Template &$template): void
	{
		$db = Database::getInstance();
		$data = $db->executeQuery("SELECT `st_user_total_number`, `st_total_upload_size`, `st_total_upload_number`, `st_average_upload_size` FROM `statistics`");
		$data = $data->fetch(PDO::FETCH_ASSOC);
		
		$stat['total_users'] = $data['st_user_total_number'];
		$stat['total_files'] = $data['st_total_upload_number'];
		$stat['total_size'] = round($data['st_total_upload_size'] / (1024*1024), 2);
		$stat['per_user'] = round($data['st_average_upload_size'] / (1024*1024), 2);
		$template->setVariable($stat);
	}
	
	// Set user allowed formats and used storage
	public function setAllowedExtensionsAndUsage(Template &$template, string $tplPath, array $user): void
	{
		$result = '';
		$allowedExt = file_get_contents($tplPath);
		
		foreach($user['allowed_extensions'] as $ext => $size) {
			$line = $allowedExt;
            $line = str_replace('{EXTENSION}', $ext, $line);
            $line = str_replace('{EXTENSION_SIZE}', $size, $line);
            $result .= $line;
		}
		
		$db = Database::getInstance();
		$var = $db->executeQuery("SELECT * from `file_size_per_user` WHERE `fl_spu_user_id`=:user_id",
									array(array(':user_id', $user['us_id'], 'integer')));
		$data = $var->fetch(PDO::FETCH_KEY_PAIR);
		
		$used = round($data[$user['us_id']] / (1024*1024), 2);

		$template->setVariable(['allowed_extension' => $result]);
		$template->setVariable(['files_limit' => $user['us_uploads_limit']]);
		$template->setVariable(['files_used' => $used]);
	}
	
	// Get statistics and usage for server.php
	public function getStatisticsAndUsage(array $user): array
	{
		$stat = array();
		
		$db = Database::getInstance();
		
		// Get statistics
		$var = $db->executeQuery("SELECT `st_user_total_number`, `st_total_upload_size`, `st_total_upload_number`, `st_average_upload_size` FROM `statistics`");
		$data = $var->fetch(PDO::FETCH_ASSOC);
		
		$stat['total_users'] = $data['st_user_total_number'];
		$stat['total_files'] = $data['st_total_upload_number'];
		$stat['total_size'] = round($data['st_total_upload_size'] / (1024*1024), 2);
		$stat['per_user'] = round($data['st_average_upload_size'] / (1024*1024), 2);
		
		// Get size used by user 
		$var = $db->executeQuery("SELECT * from `file_size_per_user` WHERE `fl_spu_user_id`=:user_id",
									array(array(':user_id', $user['us_id'], 'integer')));
		$data = $var->fetch(PDO::FETCH_KEY_PAIR);
		$used = round($data[$user['us_id']] / (1024*1024), 2);
		
		$stat['used'] = $used;
		
		return $stat;
	}
	
	// Set user files table
	public function setUserFilesTable(Template &$template, string $tplPath, array $user, string $foldersLocation): void
	{
		$result = '';
		$tableRow = file_get_contents($tplPath);
		
		$db = Database::getInstance();
		$file = $db->executeQuery("SELECT * FROM `file` WHERE `fl_user_id`=:user_id", 
									array(array(':user_id', $user['us_id'], 'integer')));
		$files = $file->fetchAll(PDO::FETCH_ASSOC);
		
		foreach ($files as $file) {
			$path = $foldersLocation. '/' . $user['us_folder'] . '/' . $file['fl_sha_name'];
			if (is_file($path)) {
				$row = $tableRow;
				$row = str_replace('{FILE_ID}', $file['fl_id'], $row);
				$row = str_replace('{FILE_NAME}', $file['fl_initial_name'], $row);
				$row = str_replace('{FILE_SIZE}', round($file['fl_size'] / 1024, 2), $row);
				$row = str_replace('{FILE_UPLOAD_TIME}', $file['fl_upload_time'], $row);
				$result .= $row;
			}
		}
		
		$template->setVariable(['user_files_table_row' => $result]);
	}
}