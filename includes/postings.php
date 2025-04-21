<?php

global $db;
$db->exec("CREATE TABLE IF NOT EXISTS posting (id INT PRIMARY KEY AUTO_INCREMENT NOT NULL, duck VARCHAR(255) NOT NULL, FOREIGN KEY (`duck`) REFERENCES duck (`id`) ON UPDATE CASCADE ON DELETE CASCADE, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, author VARCHAR(255) NOT NULL, authorIP VARCHAR(255) NOT NULL DEFAULT '127.0.0.1', content TEXT NOT NULL);");
$db->exec("CREATE TABLE IF NOT EXISTS attachments (id VARCHAR(255) NOT NULL PRIMARY KEY, posting INT NOT NULL, FOREIGN KEY (`posting`) REFERENCES posting (`id`) ON UPDATE CASCADE ON DELETE CASCADE, fileType VARCHAR(255) NOT NULL, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP)");

function getPostings(?string $duckId, int $limit = 10, int $offset = 0): array {
    global $db;
    if($duckId == null) {
        $postings = $db->queryAll("SELECT * FROM posting ORDER BY timestamp DESC LIMIT $limit OFFSET $offset;");
    } else {
        $postings = $db->queryAll("SELECT * FROM posting WHERE duck=? ORDER BY timestamp DESC LIMIT $limit OFFSET $offset;", $duckId);
    }
    return array_map(function($posting) {
        global $db;
        $posting["attachments"] = $db->queryAll("SELECT * FROM attachments WHERE posting = ?;", $posting["id"]);
        return $posting;
    }, $postings);
}

function getPosting(string|int $postingId): ?array {
    global $db;
    $posting = $db->query("SELECT * FROM posting WHERE id = ?;", $postingId);
    $posting["attachments"] = $db->queryAll("SELECT * FROM attachments WHERE posting = ?;", $postingId);
    return $posting;
}

function createPosting(string $duckId, string $content, string $author, string $authorIP): ?array
{
    global $db;
    $db->exec("INSERT INTO posting (`duck`, `author`, `authorIP`, `content`) VALUES (?, ?, ?, ?);", $duckId, $author, $authorIP, $content);
    return getPosting($db->lastInsertId());
}

function editPosting(int $postingId, string $content, string $author): void {
    global $db;
    $db->exec("UPDATE posting SET content = ?, author = ? WHERE id = ?;", $content, $author, $postingId);
}

function deletePosting(int $postingId): void {
    global $db;
    // We need to manually delete audit log entries.
    // Otherwise, the triggers won't work properly and parts of the log entries would persist.
    $db->exec("DELETE FROM audit_log_entry_posting_edit WHERE posting = ?;", $postingId);
    $db->exec("DELETE FROM posting WHERE id = ?;", $postingId);
}

function sendNotification(string $topic, array $posting): void
{
    $duck = getDuck($posting["duck"]);
    $ch = curl_init(NTFY_BASE_URL . $topic);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $posting["content"]);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode(NTFY_USERNAME . ":" . NTFY_PASSWORD),
        'Title: Neuer Tagebucheintrag für ' . $duck["displayName"] . ' von ' . $posting["author"],
        'Tags: hatched_chick,baby_chick',
        'Click: https://ducks.fs-info.de/' . $duck["id"] . '#posting-' . $posting["id"]
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}