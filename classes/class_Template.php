<?php

declare(strict_types=1);

class Template
{
    private $template = '';
    private $template_folder = '';
    private $variables = array ();

	public function setTemplate(string $templateName, string $templateFolder): void
	{
		if(!is_file($templateFolder.'/'.$templateName)) {
            throw new Exception('File [' . $templateName .'] does not exist!');
        }

        if(!is_readable($templateFolder.'/'.$templateName)) {
            throw new Exception('File [' . $templateName .'] is not readable!');
        }

        $this->template = file_get_contents($templateFolder.'/'.$templateName);
        $this->templateFolder = $templateFolder;
	}

    public function getTemplate(): string
    {
        $this->template = $this->processTemplate();
        return $this->template;
    }

    public function setVariable(array $variables)
    {
        foreach ($variables as $k => $v) {
            $this->variables[$k] = $v;
        }
        return $this->variables;
    }

    private function processTemplate(): string
    {
        while (	preg_match("/{FILE=\"([\w\.-]+)\"}/U", $this->template) ||
				preg_match("/{LBL=\"(\w+)\"}/U", $this->template) ||
				preg_match("/{DV=\"(\w+)\"}/U", $this->template)) {
			$this->template = preg_replace_callback("/{FILE=\"([\w\.-]+)\"}/U", [$this, 'processFile'], $this->template);
			$this->template = preg_replace_callback("/{LBL=\"(\w+)\"}/U", [$this, 'processLabel'], $this->template);
			$this->template = preg_replace_callback("/{DV=\"(\w+)\"}/U", [$this, 'processLabel'], $this->template);
        }
        return $this->template;
    }

    private function processFile(array $match): string
    {
        $file_name = $this->templateFolder . '/' . $match[1];

        if(!is_file($file_name)) {
            throw new Exception('Template file [' . $file_name .'] does not exist!');
        }

        if(!is_readable($file_name)) {
            throw new Exception('Template file [' . $file_name .'] is not readable!');
        }

        return file_get_contents($file_name);
    }

    private function processLabel(array $match)
    {
        $var = $match[1];
        if (isset($this->variables[$var])) {
            return $this->variables[$var];
        } else {
            throw new Exception('No variable [' . $var . '] was set!');
        }
    }
}