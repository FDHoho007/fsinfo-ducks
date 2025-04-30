<?php

class MySQLConnection
{
    private PDO $con;

    function __construct($dsn, $username = "", $password = "")
    {
        $this->con = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
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

    function lastInsertId(): false|string {
        return $this->con->lastInsertId();
    }

}