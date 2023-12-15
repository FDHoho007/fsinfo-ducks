<?php

class MySQLConnection
{
    private PDO $con;

    function __construct($dsn, $username = "", $password = "")
    {
        $this->con = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        ]);
    }

    function exec($sql, ...$params): void
    {
        $this->con->prepare($sql)->execute(array_map(function ($e) {
            return $e === false ? 0 : $e;
        }, $params));
    }

    function query($sql, ...$params): mixed
    {
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array_map(function ($e) {
            return $e === false ? 0 : $e;
        }, $params));
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r === false ? null : $r;
    }

    function queryAll($sql, ...$params): ?array
    {
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array_map(function ($e) {
            return $e === false ? 0 : $e;
        }, $params));
        $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $r === false ? null : $r;
    }

}

function randomPassword($len = 16): string
{
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $alphabetLength = strlen($alphabet)-1;
    $pass = "";
    for ($i = 0; $i < $len; $i++) {
        $pass .= $alphabet[rand(0, $alphabetLength)];
    }
    return $pass;
}

function zeroPad(int $number): string
{
    if($number < 10)
        return "0$number";
    return "$number";
}

global $db, $isAdmin, $isAuthorized;
$db = new MySQLConnection("mysql:host=" . getenv("MYSQL_HOST") . ";dbname=" . getenv("MYSQL_DATABASE"), getenv("MYSQL_USER"), getenv("MYSQL_PASSWORD"));
$db->exec("CREATE TABLE IF NOT EXISTS ducks (name VARCHAR(255) PRIMARY KEY NOT NULL, password VARCHAR(255) NOT NULL);");
$db->exec("CREATE TABLE IF NOT EXISTS diary (id INT PRIMARY KEY AUTO_INCREMENT NOT NULL, duck VARCHAR(255) NOT NULL, FOREIGN KEY (`duck`) REFERENCES ducks (`name`) ON UPDATE CASCADE ON DELETE CASCADE, timestamp DATETIME NOT NULL, author VARCHAR(255) NOT NULL, entry TEXT NOT NULL);");

$isAdmin = isset($_GET["pwd"]) && $_GET["pwd"] == getenv("ADMIN_PASSWORD");