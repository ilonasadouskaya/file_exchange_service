<?php

declare(strict_types=1);

class Database
{
    const EXCEPTION_DB_CONFIG_FILE_NOT_FOUND = 'Database file is not found!';
    const EXCEPTION_MESSAGE_NOT_FOUND = 'Message with id [{ID}] not found!';
    const EXCEPTION_FILE_NOT_ADDED_TO_DB = 'Some problem while addUploadedFileToDB() execution. File is not added to DB!';
    const FILE_UPLOAD_ALREADY_EXISTS = 'File already added to database!';

    private $servername;
    private $dbname;
    private $charset;
    private $username;
    private $password;
    private $pdo;

    public static function getInstance(): Database
    {
        static $instance = null;
        if (is_null($instance)) {
            $instance = new Database('db_credentials.php');
        }
        return $instance;
    }

    public function executeQuery(string $query, array $parameters = array())
    {
        $data = $this->pdo->prepare($query, $parameters);
		
		foreach ($parameters as $v) {
            $type = '';
            switch ($v[2]) {
                case 'boolean':
                    $type = PDO::PARAM_BOOL;
                    break;
				case 'null':
					$type = PDO::PARAM_NULL;
					break;
                case 'integer':
                    $type = PDO::PARAM_INT;
                    break;
                case 'string':
                    $type = PDO::PARAM_STR;
                    break;
                default:
                    throw new Exception('Query error. Unsupported data type in $parameters array.');
            }
            $data->bindValue($v[0], $v[1], $type);
        }
		
        $data->execute();
		
		return $data;
    }

    private function __construct(string $credentials)
    {
        if (!is_file($credentials)) {
            throw new Exception(Database::EXCEPTION_DB_CONFIG_FILE_NOT_FOUND);
        }

        require_once ($credentials);

        $this->servername = $credentials['servername'];
        $this->dbname = $credentials['dbname'];
        $this->charset = $credentials['charset'];
        $this->username = $credentials['username'];
        $this->password = $credentials['password'];

        $dsn = "mysql:host=$this->servername;dbname=$this->dbname;charset=$this->charset";

        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, array( PDO::ATTR_PERSISTENT => false,
																				PDO::ATTR_ERRMODE => true,
																				PDO::ERRMODE_EXCEPTION => true));
            return $this->pdo;
        }
        catch (PDOException $e)
        {
            die($e->getMessage());
        }
    }

    public function __destruct()
    {
        if (!is_null($this->pdo)) {
            $this->pdo = null;
        }
    }
}