<?php

namespace Library;

class DBFactory
{
	public static function MySQLPDO()
	{
		$db = new \PDO('mysql:host=localhost;dbname=iob;charset=utf8', 'root', 'root');
		$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

		return $db;
	}
	public static function MySQLMySQLi()
	{
		return new \MySQLi('localhost', 'root', 'root', 'iob');
	}
}