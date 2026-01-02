<?php

namespace Library;

class DBFactory
{
	public static function MySQLPDO()
	{
		// Support Docker environment variables ou valeurs par défaut
		$host = getenv('DB_HOST') ?: 'localhost';
		$dbname = getenv('DB_NAME') ?: 'iob';
		$user = getenv('DB_USER') ?: 'root';
		$pass = getenv('DB_PASS') ?: '';
		
		$db = new \PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
		$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

		return $db;
	}
	public static function MySQLMySQLi()
	{
		$host = getenv('DB_HOST') ?: 'localhost';
		$dbname = getenv('DB_NAME') ?: 'iob';
		$user = getenv('DB_USER') ?: 'root';
		$pass = getenv('DB_PASS') ?: '';
		
		return new \MySQLi($host, $user, $pass, $dbname);
	}
}