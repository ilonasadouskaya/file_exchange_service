<?php

declare(strict_types=1);

class File
{
	const FILENAME_OK = 0;
	const FILENAME_IS_EMPTY = -1;
	const FILENAME_TOO_LONG = -2;
	const FILE_EXTENSION_TOO_LONG = -3;
	const FILENAME_ALREADY_EXISTS = -4;
	
    const FILE_UPLOAD_OK = 0;
    const FILE_UPLOAD_EXCEED_TOTAL_LIMIT = -1;
    const FILE_UPLOAD_EXCEED_SINGLE_LIMIT = -2;
    const FILE_UPLOAD_FORBIDDEN_EXTENSION = -3;
	
	const FILE_DELETION_DELETED = 0;
	const FILE_DELETION_NOT_DELETED = -1;
	
	
	// Check if filename is OK
	public function checkFilename(array $user, string $filename, int $maxSummaryLength, int $maxExtensionLength): int
	{
		$nameLength = mb_strlen($filename, 'UTF-8');
		
		if ($nameLength == 0) {
            return File::FILENAME_IS_EMPTY;
        }
		
		if ($nameLength > $maxSummaryLength) {
            return File::FILENAME_TOO_LONG;
        }
		
		if (($nameLength - mb_strrpos($filename, '.', 0, 'UTF-8')) > $maxExtensionLength) {
            return File::FILE_EXTENSION_TOO_LONG;
        }
		
		$db = Database::getInstance();
		$data = $db->executeQuery("SELECT `fl_initial_name` FROM `file` WHERE `fl_user_id`=:user_id AND `fl_initial_name`=:filename", 
											array(array(':user_id', $user['us_id'], 'integer'), array(':filename', $filename, 'string')));
		$data = $data->fetch();
		
		if(!empty($data)) {
			return File::FILENAME_ALREADY_EXISTS;
		}
		
		return File::FILENAME_OK;		
	}

    // Delete file
    public function deleteFile(array $user, string $fileId, string $foldersLocation): int
    {
        $db = Database::getInstance();
		$file = $db->executeQuery("SELECT * FROM `file` WHERE `fl_id`=:file_id", 
									array(array(':file_id', $fileId, 'integer')));
		$file = $file->fetch(PDO::FETCH_ASSOC);
		
		if(empty($file)) {
			return File::FILE_DELETION_NOT_DELETED;
		}
		
		$path = $foldersLocation . '/' . $user['us_folder'] . '/' . $file['fl_sha_name'];

        if (is_file($path)) {
			$data = $db->executeQuery("DELETE FROM `file` WHERE `fl_id`=:file_id",
											array(array(':file_id', $fileId, 'integer')));
            unlink($path);
            return File::FILE_DELETION_DELETED;
        } else {
            return File::FILE_DELETION_NOT_DELETED;
        }
    }
	
	// Download file from storage
	public function downloadFile(array $user, string $fileId, string $foldersLocation): int
	{
		$db = Database::getInstance();
		$file = $db->executeQuery("SELECT * FROM `file` WHERE `fl_user_id`=:user_id AND `fl_id`=:file_id", 
									array(array(':user_id', $user['us_id'], 'integer'), array(':file_id', $fileId, 'integer')));
		$file = $file->fetch(PDO::FETCH_ASSOC);
		
		if(empty($file)) {
			return -1;
		}
		
		$path = $foldersLocation . '/' . $user['us_folder'] . '/' . $file['fl_sha_name'];
		
		if (!is_file($path)) {
			return -1;
		}
									
        header('Content-Disposition: attachment; filename="' . $file['fl_initial_name'] . '"');
        header('Content-type: application/octet-stream');
        $fh = fopen($path, 'rb');
        fpassthru($fh);
        return 0;
	}

    // Upload file to storage
    public function uploadFile(array $user, string $filename, float $fileSize, string $fileTmpName, string $foldersLocation): int
    {
		$allowedExtension = $user['allowed_extensions'];
		$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
		$sha1 = sha1($filename);
		$userFolder = $foldersLocation . '/' . $user['us_folder'] . '/';
		
        // If extension is allowed for this user
        if (isset($allowedExtension[$fileExt])) {
            // If file size is OK with the extension file limit
            if ($allowedExtension[$fileExt] * 1048576 >= $fileSize) {
                // If file size is OK with total size limit
                if (round($user['uploads_total_size'] + $fileSize, 2) <= $user['us_uploads_limit'] * 1048576) {
                    // Save the file with sha1 hash name to user's folder and add it to database
                    move_uploaded_file($fileTmpName, $userFolder . $sha1);
					$db = Database::getInstance();
					$add = $db->executeQuery("INSERT INTO `file`(`fl_user_id`, `fl_initial_name`, `fl_sha_name`, `fl_size`, `fl_upload_time`) 
												VALUES(:user_id, :file_initial_name, :file_sha_name, :file_size, :file_upload_time)",
													array(	array(':user_id', $user['us_id'], 'integer'),
															array(':file_initial_name', $filename, 'string'),
															array(':file_sha_name', $sha1, 'string'),
															array(':file_size', $fileSize, 'integer'),
															array(':file_upload_time', date('Y.m.d H:i:s', time()), 'string')));
                    return File::FILE_UPLOAD_OK;
                } else {
                    return File::FILE_UPLOAD_EXCEED_TOTAL_LIMIT;
                }
            } else {
                return File::FILE_UPLOAD_EXCEED_SINGLE_LIMIT;
            }
        } else {
            return File::FILE_UPLOAD_FORBIDDEN_EXTENSION;
        }
    }
	
	// Get file data for server.php
	public function getUploadedFileData(array $user, string $delete_label): array
	{
		$fileData = array();
		
		$db = Database::getInstance();
		$data = $db->executeQuery("SELECT * FROM `file` WHERE `fl_user_id`=:user_id ORDER BY `fl_id` DESC LIMIT 1", 
											array(array(':user_id', $user['us_id'], 'integer')));
		$data = $data->fetch(PDO::FETCH_ASSOC);
		
		if(empty($data)) {
			return $fileData;
		}
		
		$fileData['id'] = $data['fl_id'];
		$fileData['name'] = $data['fl_initial_name'];
		$fileData['sha_name'] = $data['fl_sha_name'];
		$fileData['size'] = round($data['fl_size'] / 1024, 2);
		$fileData['upload_time'] = $data['fl_upload_time'];
		$fileData['get_link'] = "server.php?save=true&amp;file=". $data['fl_id'];
		$fileData['delete_link'] = "server.php?delete=true&amp;file=". $data['fl_id'];
		$fileData['delete_label'] = $delete_label;
		
		return $fileData;
	}
	
}