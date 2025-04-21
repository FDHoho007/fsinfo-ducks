<?php

require "includes/autoload.php";

if(!isset($_POST["csrf_token"]) || $_POST["csrf_token"] != $_SESSION["csrf_token"]){
    http_response_code(400);
    echo("Missing csrf token!");
    exit;
}

if(!isset($_POST["action"])){
    http_response_code(400);
    echo("Missing action!");
    exit;
}
$action = $_POST["action"];

function requireParameter(string $parameterName, int $maxLength = -1) {
    if(!isset($_POST[$parameterName])) {
        http_response_code(400);
        echo("Missing $parameterName!");
        exit;
    }
    if($maxLength > 0 && strlen($_POST[$parameterName]) > $maxLength){
        http_response_code(400);
        echo(ucfirst($parameterName) . " is too long!");
        exit;
    }
    return $_POST[$parameterName];
}
function requireDuck() {
    $duckId = requireParameter("duckId");
    if(getDuck($duckId) == null){
        http_response_code(400);
        echo("Duck not found!");
        exit;
    }
    return $duckId;
}
function requirePosting() {
    $postingId = requireParameter("postingId");
    if(getPosting($postingId) == null){
        http_response_code(400);
        echo("Posting not found!");
        exit;
    }
    return $postingId;
}

if($action == "useKey") {
    echo usePassword(requireParameter("key"));
} else if($action == "logout") {
    logout();
} else if($action == "postings") {
    $duck = $_POST["duck"] ?? null;
    $limit = $_POST["limit"] ?? 10;
    $limit = intval($limit);
    $offset = $_POST["offset"] ?? 0;
    $offset = intval($offset);

    echo renderPostings(getPostings($duck, $limit, $offset));
} else if($action == "createPosting") {
    $duckId = requireDuck();
    $content = requireParameter("content", POSTING_MAX_LENGTH);
    $author = requireParameter("author", AUTHOR_MAX_LENGTH);
    // TODO: Sanitize Content and author?
    if(!isAdmin() && !isDuckedIn($duckId)) {
        http_response_code(403);
        echo("You need to be logged in to post!");
        exit;
    }
    $posting = createPosting($duckId, $content, $author, $_SERVER["REMOTE_ADDR"]);
    sendNotification(NTFY_NAMESPACE . $duckId, $posting);
    sendNotification(NTFY_TOPIC_ALL, $posting);
    echo(renderPostings([$posting]));
} else if($action == "editPosting") {
    $postingId = requirePosting();
    $posting = getPosting($postingId);
    $content = requireParameter("content", POSTING_MAX_LENGTH);
    $author = requireParameter("author", AUTHOR_MAX_LENGTH);
    if(!isAdmin() && (!isDuckedIn($posting["duck"]) || !wasCurrentlyPosted($posting["timestamp"]) || $posting["authorIP"] != $_SERVER["REMOTE_ADDR"])) {
        http_response_code(403);
        echo("You can only edit your own postings for " . POST_EDIT_TIMEFRAME . " minutes!");
        exit;
    }
    editPosting($postingId, $content, $author);
    auditPostingEdit($postingId, $posting["content"], $content, $posting["author"], $author);
} else if($action == "deletePosting") {
    if(!isAdmin()) {
        http_response_code(403);
        echo("You need to be an admin to delete postings!");
        exit;
    }
    deletePosting(requirePosting());
} else if($action == "editBiography") {
    $duckId = requireDuck();
    $duck = getDuck($duckId);
    $biography = requireParameter("biography", BIOGRAPHY_MAX_LENGTH);
    if(!isAdmin() && (!isDuckedIn($duckId) || !isModerator())){
        http_response_code(403);
        echo("You are not allowed to modify this duck's biography!");
        exit;
    }
    setDuckBiography($duckId, $biography);
    auditDuckBio($duckId, $duck["biography"], $biography == "" ? null : $biography);
} else if($action == "lockDuck") {
    if(!isAdmin()) {
        http_response_code(403);
        echo("You need to be an admin to lock ducks!");
        exit;
    }
    $duckId = requireDuck();
    $lockedBefore = getDuck($duckId)["locked"];
    setDuckLocked($duckId, true);
    auditDuckLock($duckId, $lockedBefore, true);
} else if($action == "unlockDuck") {
    if(!isAdmin()) {
        http_response_code(403);
        echo("You need to be an admin to unlock ducks!");
        exit;
    }
    $duckId = requireDuck();
    $lockedBefore = getDuck($duckId)["locked"];
    setDuckLocked($duckId, false);
    auditDuckLock($duckId, $lockedBefore, false);
}