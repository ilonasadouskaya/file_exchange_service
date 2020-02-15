-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 06, 2019 at 03:29 PM
-- Server version: 8.0.17
-- PHP Version: 7.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `database`
--

-- --------------------------------------------------------

--
-- Table structure for table `allowed_format`
--

DROP TABLE IF EXISTS `allowed_format`;
CREATE TABLE IF NOT EXISTS `allowed_format` (
  `allw_frm_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `allw_frm_user_id` int(10) UNSIGNED NOT NULL,
  `allw_frm_format_id` int(10) UNSIGNED NOT NULL,
  `allw_frm_format_max_size` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`allw_frm_id`),
  UNIQUE KEY `UNQ_user_format` (`allw_frm_user_id`,`allw_frm_format_id`),
  KEY `IXFK_allowed_format_user` (`allw_frm_user_id`),
  KEY `IXFK_allowed_formats_all_formats` (`allw_frm_format_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `allowed_format`
--

INSERT INTO `allowed_format` (`allw_frm_id`, `allw_frm_user_id`, `allw_frm_format_id`, `allw_frm_format_max_size`) VALUES
(1, 5, 1, 10),
(2, 5, 2, 10),
(3, 5, 3, 40),
(4, 6, 1, 5),
(5, 6, 3, 50),
(6, 6, 4, 2),
(7, 6, 5, 3);

--
-- Triggers `allowed_format`
--
DROP TRIGGER IF EXISTS `allw_frm_before_insert`;
DELIMITER $$
CREATE TRIGGER `allw_frm_before_insert` BEFORE INSERT ON `allowed_format` FOR EACH ROW BEGIN
	IF NEW.`allw_frm_format_id` NOT IN (SELECT `frm_id` FROM `all_formats`) THEN
    	SIGNAL SQLSTATE '49000' SET MESSAGE_TEXT = 'New allw_frm_id should be equal to some frm_id', MYSQL_ERRNO = 5001;
    END IF;
	IF NEW.`allw_frm_user_id` NOT IN (SELECT `us_id` FROM `user`) THEN
    	SIGNAL SQLSTATE '48000' SET MESSAGE_TEXT = 'New allw_frm_user_id should be equal to some us_id', MYSQL_ERRNO = 4001;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `all_formats`
--

DROP TABLE IF EXISTS `all_formats`;
CREATE TABLE IF NOT EXISTS `all_formats` (
  `frm_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `frm_name` varchar(50) NOT NULL,
  PRIMARY KEY (`frm_id`),
  UNIQUE KEY `UNQ_format` (`frm_name`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `all_formats`
--

INSERT INTO `all_formats` (`frm_id`, `frm_name`) VALUES
(1, 'docx'),
(2, 'xlsx'),
(3, 'zip'),
(4, 'jpg'),
(5, 'png');

-- --------------------------------------------------------

--
-- Table structure for table `configuration`
--

DROP TABLE IF EXISTS `configuration`;
CREATE TABLE IF NOT EXISTS `configuration` (
  `cnf_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cnf_folders_location` varchar(1000) NOT NULL,
  `cnf_templates_location` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `cnf_headers_location` varchar(1000) NOT NULL,
  `cnf_file_max_summary_length` int(11) NOT NULL,
  `cnf_file_max_extension_length` int(11) NOT NULL,
  `cnf_copyright_start_year` int(11) NOT NULL,
  PRIMARY KEY (`cnf_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `configuration`
--

INSERT INTO `configuration` (`cnf_id`, `cnf_folders_location`, `cnf_templates_location`, `cnf_headers_location`, `cnf_file_max_summary_length`, `cnf_file_max_extension_length`, `cnf_copyright_start_year`) VALUES
(1, 'users_folders', 'templates', 'headers', 200, 50, 2019);

-- --------------------------------------------------------

--
-- Table structure for table `error_message`
--

DROP TABLE IF EXISTS `error_message`;
CREATE TABLE IF NOT EXISTS `error_message` (
  `err_m_id` varchar(150) NOT NULL,
  `err_m_text` varchar(1000) NOT NULL,
  PRIMARY KEY (`err_m_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `error_message`
--

INSERT INTO `error_message` (`err_m_id`, `err_m_text`) VALUES
('CREDENTIALS_RESULT_BAD_LOGIN', 'Incorrect login!'),
('FILENAME_TOO_LONG', 'Filename exceeds total file length limit!'),
('CREDENTIALS_RESULT_BAD_LOGIN_OR_PASSWORD', 'Incorrect login and/or password!'),
('CREDENTIALS_RESULT_EMPTY', 'Fill in both login and password fields!'),
('FILENAME_IS_EMPTY', 'Filename is empty!'),
('CREDENTIALS_RESULT_FOLDER_IS_MISSING', 'Sorry, your home directory is missing!'),
('FILE_UPLOAD_EXCEED_TOTAL_LIMIT', 'File is not uploaded! Total folder limit is exceeded, if upload the file.'),
('FILE_UPLOAD_EXCEED_SINGLE_LIMIT', 'File is not uploaded! Size limit for extension is exceeded in the file.'),
('FILE_UPLOAD_FORBIDDEN_EXTENSION', 'File is not uploaded! The file extension is not allowed for upload.'),
('FILE_EXTENSION_TOO_LONG', 'File extension exceeds extention limit!'),
('FILENAME_ALREADY_EXISTS', 'File with such name already exists!'),
('FILE_DELETION_NOT_DELETED', 'File is not deleted! Some server error.'),
('FILE_UPLOAD_NOT_CHOSEN', 'Choose a file for upload!');

-- --------------------------------------------------------

--
-- Table structure for table `file`
--

DROP TABLE IF EXISTS `file`;
CREATE TABLE IF NOT EXISTS `file` (
  `fl_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fl_user_id` int(10) UNSIGNED NOT NULL,
  `fl_initial_name` varchar(1000) NOT NULL,
  `fl_sha_name` varchar(40) NOT NULL,
  `fl_size` int(10) UNSIGNED NOT NULL,
  `fl_upload_time` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`fl_id`),
  KEY `IXFK_file_file_size_per_user` (`fl_user_id`),
  KEY `IXFK_file_user` (`fl_user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=401 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `file`
--

INSERT INTO `file` (`fl_id`, `fl_user_id`, `fl_initial_name`, `fl_sha_name`, `fl_size`, `fl_upload_time`) VALUES
(280, 5, 'task07.docx', '903d90cadf06e4365f58abaa9cb85b9dec1525b3', 32430, '2019.10.05 10:09:58'),
(174, 5, 'timetable_php_06.docx', '04c74046b874846656f7a074712910281e5a7009', 31577, '2019.09.29 11:07:07'),
(400, 6, '02.01-AP003.zip', 'fda2d5fe6cb06e8c8cc9d60023e874adc76d5824', 818393, '2019.10.06 15:22:50'),
(398, 6, '02.05-AP007.zip', '74ca4c044d3a977a1be56231aa6b5574b8c8dda2', 41497, '2019.10.06 15:10:14');

--
-- Triggers `file`
--
DROP TRIGGER IF EXISTS `file_after_delete`;
DELIMITER $$
CREATE TRIGGER `file_after_delete` AFTER DELETE ON `file` FOR EACH ROW BEGIN    
    UPDATE `file_size_per_user`
    SET `fl_spu_total_size` = `fl_spu_total_size` - OLD.`fl_size`
    	WHERE `fl_spu_user_id` = OLD.`fl_user_id`;
	IF (SELECT SUM(`fl_size`) FROM `file`) IS NULL THEN
    	UPDATE `statistics`
        SET `st_total_upload_size` = 0,
            `st_total_upload_number` = 0,
            `st_average_upload_size` = 0;
    ELSE 
        UPDATE `statistics`
        SET `st_total_upload_size` = (SELECT SUM(`fl_size`) FROM `file`),
            `st_total_upload_number` = (SELECT COUNT(`fl_sha_name`) FROM `file`),
            `st_average_upload_size` = `st_total_upload_size` / `st_user_total_number`;
    END IF;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `file_after_insert`;
DELIMITER $$
CREATE TRIGGER `file_after_insert` AFTER INSERT ON `file` FOR EACH ROW BEGIN
	UPDATE `file_size_per_user`
    SET `fl_spu_total_size` = `fl_spu_total_size` + NEW.`fl_size`
    	WHERE `fl_spu_user_id` = NEW.`fl_user_id`;
	UPDATE `statistics`
    SET `st_total_upload_size` = (SELECT SUM(`fl_size`) FROM `file`),
    	`st_total_upload_number` = (SELECT COUNT(`fl_sha_name`) FROM `file`),
        `st_average_upload_size` = `st_total_upload_size` / `st_user_total_number`;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `file_before_insert`;
DELIMITER $$
CREATE TRIGGER `file_before_insert` BEFORE INSERT ON `file` FOR EACH ROW BEGIN
	IF NEW.`fl_user_id` NOT IN (SELECT `us_id` FROM `user`) THEN
    	SIGNAL SQLSTATE '50000' SET MESSAGE_TEXT = 'New fl_id should be equal to some us_id', MYSQL_ERRNO = 6001;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `file_size_per_user`
--

DROP TABLE IF EXISTS `file_size_per_user`;
CREATE TABLE IF NOT EXISTS `file_size_per_user` (
  `fl_spu_user_id` int(10) UNSIGNED NOT NULL,
  `fl_spu_total_size` int(11) NOT NULL,
  PRIMARY KEY (`fl_spu_user_id`),
  UNIQUE KEY `UNQ_user` (`fl_spu_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `file_size_per_user`
--

INSERT INTO `file_size_per_user` (`fl_spu_user_id`, `fl_spu_total_size`) VALUES
(5, 64007),
(6, 859890);

-- --------------------------------------------------------

--
-- Table structure for table `statistics`
--

DROP TABLE IF EXISTS `statistics`;
CREATE TABLE IF NOT EXISTS `statistics` (
  `st_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `st_user_total_number` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `st_total_upload_size` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `st_total_upload_number` int(11) NOT NULL DEFAULT '0',
  `st_average_upload_size` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`st_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `statistics`
--

INSERT INTO `statistics` (`st_id`, `st_user_total_number`, `st_total_upload_size`, `st_total_upload_number`, `st_average_upload_size`) VALUES
(1, 2, 923897, 4, 461949);

-- --------------------------------------------------------

--
-- Table structure for table `template_config`
--

DROP TABLE IF EXISTS `template_config`;
CREATE TABLE IF NOT EXISTS `template_config` (
  `tpl_cnf_id` int(11) NOT NULL AUTO_INCREMENT,
  `tpl_cnf_page` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `tpl_cnf_template` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `tpl_cnf_header` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`tpl_cnf_id`),
  UNIQUE KEY `cnf_page` (`tpl_cnf_page`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `template_config`
--

INSERT INTO `template_config` (`tpl_cnf_id`, `tpl_cnf_page`, `tpl_cnf_template`, `tpl_cnf_header`) VALUES
(1, 'login', '_login_page.tpl', 'login_page.php'),
(2, 'main', '_main_page.tpl', 'main_page.php');

-- --------------------------------------------------------

--
-- Table structure for table `template_lbls`
--

DROP TABLE IF EXISTS `template_lbls`;
CREATE TABLE IF NOT EXISTS `template_lbls` (
  `lbl_id` int(11) NOT NULL AUTO_INCREMENT,
  `lbl_name` varchar(300) NOT NULL,
  `lbl_value` varchar(1000) NOT NULL,
  PRIMARY KEY (`lbl_id`),
  UNIQUE KEY `lbl_name` (`lbl_name`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `template_lbls`
--

INSERT INTO `template_lbls` (`lbl_id`, `lbl_name`, `lbl_value`) VALUES
(1, 'header_not_logged', 'Log In is required!'),
(2, 'login', 'Login'),
(3, 'password', 'Password'),
(4, 'remember', 'Remember me'),
(5, 'statistics', 'Statistics'),
(6, 'users_total', 'Users total'),
(7, 'files_total', 'Files total'),
(8, 'size_total', 'Size total'),
(9, 'size_per_user', 'Size per user'),
(10, 'file_size_unit', 'Mb'),
(11, 'organization', 'EPAM'),
(12, 'object', 'PHP Training'),
(13, 'header_logged', 'You are logged in as'),
(14, 'allowed_formats', 'Allowed to download'),
(15, 'up_to', 'up to'),
(16, 'used', 'Used'),
(17, 'out_of', 'out of'),
(18, 'download_form', 'Download from a remote server'),
(19, 'save_to_stor', 'Save to my storage'),
(20, 'upload_form', 'Upload new file'),
(21, 'file', 'File'),
(22, 'size', 'Size'),
(23, 'file_size_unit_table', 'Kb'),
(24, 'upload_time', 'Time of upload'),
(25, 'delete', 'Delete'),
(26, 'logout', 'Log Out');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `us_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `us_login` varchar(100) NOT NULL,
  `us_password` binary(40) NOT NULL,
  `us_email` varchar(150) NOT NULL,
  `us_name` varchar(150) NOT NULL,
  `us_folder` varchar(150) DEFAULT NULL,
  `us_uploads_limit` int(10) UNSIGNED NOT NULL,
  `us_cookies` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`us_id`),
  UNIQUE KEY `UNQ_email` (`us_email`),
  UNIQUE KEY `UNQ_login` (`us_login`),
  UNIQUE KEY `UNQ_folder` (`us_folder`),
  KEY `IXFK_user_file_size_per_user` (`us_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`us_id`, `us_login`, `us_password`, `us_email`, `us_name`, `us_folder`, `us_uploads_limit`, `us_cookies`) VALUES
(5, 'user', 0x38636232323337643036373963613838646236343634656163363064613936333435353133393634, 'user1@email.com', 'user1', 'user1_', 100, ''),
(6, '123', 0x65333861643231343934336461616431643634633130326661656332396465346166653964613364, 'email', '123', '123_folder', 120, '');

--
-- Triggers `user`
--
DROP TRIGGER IF EXISTS `user_after_delete`;
DELIMITER $$
CREATE TRIGGER `user_after_delete` AFTER DELETE ON `user` FOR EACH ROW BEGIN
	DELETE FROM `allowed_format`
    	WHERE `allw_frm_user_id` = OLD.`us_id`;
	DELETE FROM `file`
    	WHERE `fl_user_id` = OLD.`us_id`;
	DELETE FROM `file_size_per_user`
		WHERE `fl_spu_user_id` = OLD.`us_id`;
        
    IF (SELECT COUNT(`us_id`) FROM `user`) IS NULL THEN 
        UPDATE `statistics`
    	SET `st_user_total_number` = 0,   
		`st_total_upload_size` = 0,
    	`st_total_upload_number` = 0,
        `st_average_upload_size` = 0;
    ELSE
    	UPDATE `statistics`
        SET `st_user_total_number` = (SELECT COUNT(`us_id`) FROM `user`),   
		`st_total_upload_size` = (SELECT SUM(`fl_size`) FROM `file`),
    	`st_total_upload_number` = (SELECT COUNT(`fl_sha_name`) FROM `file`),
        `st_average_upload_size` = `st_total_upload_size` / `st_user_total_number`;  
    END IF;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `user_after_insert`;
DELIMITER $$
CREATE TRIGGER `user_after_insert` AFTER INSERT ON `user` FOR EACH ROW BEGIN
	INSERT INTO `file_size_per_user`(`fl_spu_user_id`, `fl_spu_total_size`) VALUES (NEW.us_id, 0);
    IF (SELECT SUM(`fl_size`) FROM `file`) IS NULL THEN
        UPDATE `statistics`
        SET `st_user_total_number` = (SELECT COUNT(`us_id`) FROM `user`),
            `st_total_upload_size` = 0,
            `st_total_upload_number` = 0,
            `st_average_upload_size` = 0;
    ELSE
        UPDATE `statistics`
        SET `st_user_total_number` = (SELECT COUNT(`us_id`) FROM `user`),
            `st_total_upload_size` = (SELECT SUM(`fl_size`) FROM `file`),
            `st_total_upload_number` = (SELECT COUNT(`fl_sha_name`) FROM `file`),
            `st_average_upload_size` = `st_total_upload_size` / `st_user_total_number`;
    END IF;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `user_before_insert`;
DELIMITER $$
CREATE TRIGGER `user_before_insert` BEFORE INSERT ON `user` FOR EACH ROW BEGIN
	IF NEW.`us_login` = NEW.`us_email` THEN
	SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Login and email can not be equal!', MYSQL_ERRNO = 1001;
	END IF;
	IF NEW.`us_login` = NEW.`us_password` THEN
	SIGNAL SQLSTATE '46000' SET MESSAGE_TEXT = 'Login and password can not be equal!', MYSQL_ERRNO = 2001;
	END IF;
	IF NEW.`us_password` = NEW.`us_email` THEN
	SIGNAL SQLSTATE '47000' SET MESSAGE_TEXT = 'Password and email can not be equal!', MYSQL_ERRNO = 3001;
	END IF;
END
$$
DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
