<?php

declare(strict_types=1);

class Config
{
    private $config = array();

    public function __construct()
    {
		$db = Database::getInstance();
		
		$var = $db->executeQuery("SELECT * from `configuration`");
		$this->config['general_configuration'] = $var->fetch(PDO::FETCH_ASSOC);
		
		$var = $db->executeQuery("SELECT * from `error_message`");
		$this->config['error_message'] = $var->fetchAll(PDO::FETCH_KEY_PAIR);
		
		$var = $db->executeQuery("SELECT `tpl_cnf_page`, `tpl_cnf_template` FROM `template_config`");
		$this->config['template_configuration'] = $var->fetchAll(PDO::FETCH_KEY_PAIR);
		
		$var = $db->executeQuery("SELECT `lbl_name`, `lbl_value` FROM `template_lbls`");
		$this->config['template_lables'] = $var->fetchAll(PDO::FETCH_KEY_PAIR);
    }
	
	public function getFoldersLocation(): string
    {
        if($this->config['general_configuration']['cnf_folders_location'] == "") {
			return $_SERVER['DOCUMENT_ROOT'];
		} else {
			return $this->config['general_configuration']['cnf_folders_location'];
		}
    }
	
	public function getTemplatesLocation(): string
    {
        if($this->config['general_configuration']['cnf_templates_location'] == "") {
			return $_SERVER['DOCUMENT_ROOT'] . '/templates';
		} else {
			return $this->config['general_configuration']['cnf_templates_location'];
		}
    }
	
	public function getPageTemplateName(string $pageName)
	{
		if(!isset($this->config['template_configuration'][$pageName])) {
			throw new Exception("No template specified for [" . $pageName . "]!");
		} else {
			return $this->config['template_configuration'][$pageName];
		}
	}
	
	public function getAllLabels(): array
	{
		if(!isset($this->config['template_lables'])) {
			throw new  Exception("No template labels specified!");
		} else {
			return $this->config['template_lables'];
		}
	}
	
	public function getLabelByName(string $labelName): string
	{
		if(!isset($this->config['template_lables'])) {
			throw new  Exception("No template labels specified!");
		} else {
			return $this->config['template_lables'][$labelName];
		}
	}
	
	public function getMessageById(string $messageId): string
	{
		if(!isset($this->config['error_message'][$messageId])) {
			throw new  Exception("No message with [" . $messageId . "] specified!");
		} else {
			return $this->config['error_message'][$messageId];
		}
	}
	
	public function getFileLengthLimits(): array
    {
        if(!isset($this->config['general_configuration']['cnf_file_max_summary_length']) && 
			!isset($this->config['general_configuration']['cnf_file_max_extension_length'])) {
			
			throw new Exception("No file length limits specified!");
		} else {
			$lengthLimits['max_summary_length'] = $this->config['general_configuration']["cnf_file_max_summary_length"];
			$lengthLimits['max_extension_length'] = $this->config['general_configuration']["cnf_file_max_extension_length"];
			return $lengthLimits;
		}
    }
	
	public function getCopyrightYears(): string
    {
        if(!isset($this->config['general_configuration']['cnf_copyright_start_year'])) {
			throw new Exception("No copyright start year specified!");
		} else {
			$copyrightStartYear = $this->config['general_configuration']['cnf_copyright_start_year'];
		}
		
        if ($copyrightStartYear == date('Y')) {
            return $copyrightStartYear;
        } else {
            return $copyrightStartYear . '&ndash;' . date('Y');
        }
    }

}