<?php

global $db;
$db->exec("CREATE TABLE IF NOT EXISTS audit_log_entry (id INT PRIMARY KEY AUTO_INCREMENT NOT NULL, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, moderator BOOLEAN NOT NULL, admin BOOLEAN NOT NULL, ip VARCHAR(255) NOT NULL DEFAULT '127.0.0.1');");
$db->exec("CREATE TABLE IF NOT EXISTS audit_log_entry_duck (id INT NOT NULL, FOREIGN KEY (`id`) REFERENCES audit_log_entry (`id`) ON UPDATE CASCADE ON DELETE CASCADE, duck VARCHAR(255) NOT NULL, FOREIGN KEY (`duck`) REFERENCES duck (`id`) ON UPDATE CASCADE ON DELETE CASCADE);");
$db->exec("CREATE TRIGGER IF NOT EXISTS delete_audit_log_entry_after_duck AFTER DELETE ON audit_log_entry_duck FOR EACH ROW BEGIN DELETE FROM audit_log_entry WHERE audit_log_entry.id = OLD.id; END;");
$db->exec("CREATE TABLE IF NOT EXISTS audit_log_entry_duck_lock (id INT NOT NULL, FOREIGN KEY (`id`) REFERENCES audit_log_entry (`id`) ON UPDATE CASCADE ON DELETE CASCADE, lockedBefore BOOLEAN NOT NULL, lockedAfter BOOLEAN NOT NULL);");
$db->exec("CREATE TABLE IF NOT EXISTS audit_log_entry_duck_bio (id INT NOT NULL, FOREIGN KEY (`id`) REFERENCES audit_log_entry (`id`) ON UPDATE CASCADE ON DELETE CASCADE, oldBio TEXT, newBio TEXT);");
$db->exec("CREATE TABLE IF NOT EXISTS audit_log_entry_posting_edit (id INT NOT NULL, FOREIGN KEY (`id`) REFERENCES audit_log_entry (`id`) ON UPDATE CASCADE ON DELETE CASCADE, posting INT NOT NULL, FOREIGN KEY (`posting`) REFERENCES posting (`id`) ON UPDATE CASCADE ON DELETE CASCADE, oldContent TEXT NOT NULL, newContent TEXT NOT NULL, oldAuthor TEXT NOT NULL, newAuthor TEXT NOT NULL);");
$db->exec("CREATE TRIGGER IF NOT EXISTS delete_audit_log_entry_after_posting AFTER DELETE ON audit_log_entry_posting_edit FOR EACH ROW BEGIN DELETE FROM audit_log_entry WHERE audit_log_entry.id = OLD.id; END;");
$db->exec("CREATE VIEW IF NOT EXISTS audit_log AS SELECT audit_log_entry.id AS id, duck, lockedBefore, lockedAfter, oldBio, newBio, posting, oldContent, newContent, oldAuthor, newAuthor, `timestamp`, moderator, admin, ip FROM audit_log_entry LEFT JOIN audit_log_entry_duck ON audit_log_entry.id = audit_log_entry_duck.id LEFT JOIN audit_log_entry_duck_lock ON audit_log_entry.id = audit_log_entry_duck_lock.id LEFT JOIN audit_log_entry_duck_bio ON audit_log_entry.id = audit_log_entry_duck_bio.id LEFT JOIN audit_log_entry_posting_edit ON audit_log_entry.id = audit_log_entry_posting_edit.id;");

function emptyAuditEntry(): string {
    global $db;
    $db->exec("INSERT INTO audit_log_entry (`moderator`, `admin`, `ip`) VALUES (?, ?, ?);", isModerator(), isAdmin(), $_SERVER["REMOTE_ADDR"]);
    return $db->lastInsertId();
}

function auditDuckLock(string $duckId, bool $lockedBefore, bool $lockedAfter): void {
    global $db;
    $entryId = emptyAuditEntry();
    $db->exec("INSERT INTO audit_log_entry_duck (`id`, `duck`) VALUES (?, ?);", $entryId, $duckId);
    $db->exec("INSERT INTO audit_log_entry_duck_lock (`id`, `lockedBefore`, `lockedAfter`) VALUES (?, ?, ?);", $entryId, $lockedBefore, $lockedAfter);
}

function auditDuckBio(string $duckId, ?string $oldBio, ?string $newBio): void {
    global $db;
    $entryId = emptyAuditEntry();
    $db->exec("INSERT INTO audit_log_entry_duck (`id`, `duck`) VALUES (?, ?);", $entryId, $duckId);
    $db->exec("INSERT INTO audit_log_entry_duck_bio (`id`, `oldBio`, `newBio`) VALUES (?, ?, ?);", $entryId, $oldBio, $newBio);
}

function auditPostingEdit(string $postingId, string $oldContent, string $newContent, string $oldAuthor, string $newAuthor): void {
    global $db;
    $entryId = emptyAuditEntry();
    $db->exec("INSERT INTO audit_log_entry_posting_edit (`id`, `posting`, `oldContent`, `newContent`, `oldAuthor`, `newAuthor`) VALUES (?, ?, ?, ?, ?, ?);", $entryId, $postingId, $oldContent, $newContent, $oldAuthor, $newAuthor);
}