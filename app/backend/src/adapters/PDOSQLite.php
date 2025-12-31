<?php namespace Main;

    use PDO; 
    
    class PDOSQLite { 
        public static function open(string $file): PDO { 
            $root = dirname(dirname(dirname(__FILE__)));
            $pdo = new PDO("sqlite:{$root}/{$file}");
            $pdo->setAttribute(PDO::SQLITE_ATTR_OPEN_FLAGS, Pdo\Sqlite::OPEN_READWRITE);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        }

    }
