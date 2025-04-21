<?php

global $db;
$db->exec("CREATE TABLE IF NOT EXISTS duck (id VARCHAR(255) PRIMARY KEY NOT NULL, displayName VARCHAR(255) NOT NULL, picture VARCHAR(255), biography TEXT, password VARCHAR(255) NOT NULL, locked BOOLEAN NOT NULL DEFAULT FALSE);");

function getDucks(): array {
    global $db;
    $ducks = [];
    foreach ($db->queryAll("SELECT * FROM duck;") as $duck) {
        if($duck["picture"] == null) {
            $duck["picture"] = "/uploads/" . DEFAULT_DUCK_PICTURE . ".png";
        } else {
            $duck["picture"] = "/uploads/" . $duck["picture"] . ".png";
        }
        $ducks[] = $duck;
    }
    return $ducks;
}

function getDuck(string $duckId): ?array {
    global $db;
    $duck = $db->query("SELECT * FROM duck WHERE id = ?;", $duckId);
    if($duck == null) {
        return null;
    }
    if($duck["picture"] == null) {
        $duck["picture"] = "/uploads/" . DEFAULT_DUCK_PICTURE . ".png";
    } else {
        $duck["picture"] = "/uploads/" . $duck["picture"] . ".png";
    }
    return $duck;
}

function setDuckBiography(string $duckId, string $biography): void {
    global $db;
    $db->exec("UPDATE duck SET biography = ? WHERE id = ?;", $biography == "" ? null : $biography, $duckId);
}

function setDuckLocked(string $duckId, bool $locked): void {
    global $db;
    $db->exec("UPDATE duck SET locked = ? WHERE id = ?;", $locked, $duckId);
}