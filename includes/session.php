<?php

session_set_cookie_params(time()+60*60*24*30);
session_start();
if(!isset($_SESSION["csrf_token"])){
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
foreach (array_keys($_SESSION) as $key => $value) {
    if(str_starts_with($key, "duck_") && $value + SESSION_TIME < time()) {
        unset($_SESSION[$key]);
    }
}

function isAdmin(): bool
{
    return isset($_SESSION["admin"]) && $_SESSION["admin"] == 1;
}

function isModerator(): bool
{
    return isset($_SESSION["moderator"]) && $_SESSION["moderator"] == 1;
}

function isDuckedIn(string $duckId): bool
{
    return isset($_SESSION["duck_$duckId"]) && $_SESSION["duck_$duckId"] == 1 && !getDuck($duckId)["locked"];
}

function usePassword(string $password): string
{
    if ($password == getenv("ADMIN_PASSWORD")) {
        $_SESSION["admin"] = 1;
        return "admin";
    }
    if ($password == getenv("MODERATOR_PASSWORD")) {
        $_SESSION["moderator"] = 1;
        return "moderator";
    }
    foreach (getDucks() as $duck) {
        if ($duck["password"] == $password && !$duck["locked"]) {
            $_SESSION["duck_" . $duck["id"]] = time();
            return $duck["id"];
        }
    }
    return "false";
}

function logout(): void {
    if(isset($_SESSION["admin"])) {
        unset($_SESSION["admin"]);
    }
    if(isset($_SESSION["moderator"])) {
        unset($_SESSION["moderator"]);
    }
    foreach (getDucks() as $duck) {
        if(isset($_SESSION["duck_" . $duck["id"]])) {
            unset($_SESSION["duck_" . $duck["id"]]);
        }
    }
}