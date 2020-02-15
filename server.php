<?php

declare(strict_types=1);

error_reporting(0);
session_start();

if (!isset($_SESSION['logged'])) {
    $response = array('code' => -1, 'message' => 'You are not authorised!');
    echo json_encode($response);
    exit();
}

spl_autoload_register(function (string $class) {
    require_once ('classes/class_' . $class . '.php');
});

$config = new Config();
$file = new File();
$handler = new Handler();

$response['code'] = 0;
$response['message'] = '';


// Delete file
if (isset($_GET['delete']) && isset($_GET['file']) && ($_GET['delete'] == 'true')) {
	$deleteFile = $file->deleteFile($_SESSION['logged'], $_GET['file'], $config->getFoldersLocation());
	$response['code'] = $deleteFile;
	if ($deleteFile === File::FILE_DELETION_NOT_DELETED) {
		$response['message'] = $config->getMessageById('FILE_DELETION_NOT_DELETED');
	} 
} 
// If no file is chosen for upload, but upload button is clicked
elseif ((!isset($_FILES['file'])) && !isset($_GET['delete']) && !isset($_GET['save'])) {
	$response['code'] = -100;
	$response['message'] = $config->getMessageById('FILE_UPLOAD_NOT_CHOSEN');
} 
// Upload new file to storage  
elseif ((isset($_FILES['file'])) && ($_FILES['file']['error'] == 0)) {
    $lengthLimits = $config->getFileLengthLimits();
	$checkFilename = $file->checkFilename(	$_SESSION['logged'], 
											$_FILES['file']['name'], 
											(int)$lengthLimits['max_summary_length'], 
											(int)$lengthLimits['max_extension_length']);
	$response['code'] = $checkFilename;
	if ($checkFilename === File::FILENAME_IS_EMPTY) {
		$response['message'] = $config->getMessageById('FILENAME_IS_EMPTY');
	} elseif ($checkFilename === File::FILENAME_TOO_LONG) {
		$response['message'] = $config->getMessageById('FILENAME_TOO_LONG');
	} elseif ($checkFilename === File::FILE_EXTENSION_TOO_LONG) {
		$response['message'] =  $config->getMessageById('FILE_EXTENSION_TOO_LONG');
	} elseif ($checkFilename === File::FILENAME_ALREADY_EXISTS) {
		$response['message'] = $config->getMessageById('FILENAME_ALREADY_EXISTS');
	} elseif ($checkFilename === File::FILENAME_OK) {
		$uploadFile = $file->uploadFile($_SESSION['logged'], 
										$_FILES['file']['name'], 
										$_FILES['file']['size'], 
										$_FILES['file']['tmp_name'],
										$config->getFoldersLocation());
		$response['code'] = $uploadFile;
		if ($uploadFile === File::FILE_UPLOAD_EXCEED_TOTAL_LIMIT) {
			$response['message'] = $config->getMessageById('FILE_UPLOAD_EXCEED_TOTAL_LIMIT');
		} elseif ($uploadFile === File::FILE_UPLOAD_EXCEED_SINGLE_LIMIT) {
			$response['message'] = $config->getMessageById('FILE_UPLOAD_EXCEED_SINGLE_LIMIT');
		} elseif ($uploadFile === File::FILE_UPLOAD_FORBIDDEN_EXTENSION) {
			$response['message'] = $config->getMessageById('FILE_UPLOAD_FORBIDDEN_EXTENSION');
		} elseif ($uploadFile === File::FILE_UPLOAD_OK) {
			$response['fileData'] = $file->getUploadedFileData($_SESSION['logged'], $config->getLabelByName('delete'));
		}
	}
}
// Check if file exists in database and storage
elseif (isset($_GET['save']) && isset($_GET['file']) && ($_GET['save'] == 'check')) {
	$db = Database::getInstance();
	$file = $db->executeQuery("SELECT * FROM `file` WHERE `fl_user_id`=:user_id AND `fl_id`=:file_id", 
								array(array(':user_id', $_SESSION['logged']['us_id'], 'integer'), array(':file_id', $_GET['file'], 'integer')));
	$file = $file->fetch(PDO::FETCH_ASSOC);
	
	if(empty($file)) {
		$response['code'] = -1;
		$response['message'] = $config->getMessageById('FILE_DOWNLOAD_BAD');
	}
	
	$path = $config->getFoldersLocation() . '/' . $_SESSION['logged']['us_folder'] . '/' . $file['fl_sha_name'];
	
	if (!is_file($path)) {
		$response['code'] = -1;
		$response['message'] = $config->getMessageById('FILE_DOWNLOAD_BAD');
	}
}
// Download file from storage
elseif (isset($_GET['save']) && isset($_GET['file']) && ($_GET['save'] == 'true')) {
	$saveFile = $file->downloadFile($_SESSION['logged'], $_GET['file'], $config->getFoldersLocation());
	$response['code'] = $saveFile;
}

$response['statistics'] = $handler->getStatisticsAndUsage($_SESSION['logged']);
echo json_encode($response);