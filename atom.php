<?php

require_once "includes/autoload.php";

const PAGE_SIZE = 20;
$duckId = null;
$page = $_GET['page'] ?? 1;
if(isset($_GET["duck"])) {
    if(getDuck($_GET["duck"]) == null) {
        http_response_code(404);
        echo("Duck not found!");
        exit;
    }
    $duckId = $_GET["duck"];
}
$postings = getPostings($duckId, PAGE_SIZE, ($page-1)*PAGE_SIZE);

header("Content-Type: application/atom+xml");
echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
echo("<feed xmlns=\"http://www.w3.org/2005/Atom\">");
echo("<title>Enten Tagebücher</title>");
echo("<link href=\"https://ducks.fs-info.de/\" rel=\"alternate\" />");
echo("<link href=\"https://ducks.fs-info.de" . $_SERVER["REQUEST_URI"] . "\" rel=\"self\" />");
if(sizeof($postings) == PAGE_SIZE) {
    echo("<link href=\"https://ducks.fs-info.de/atom.php?" . ($duckId == null ? "" : "duck=$duckId&") . "page=" . ($page + 1) . "\" rel=\"next\" />");
}
echo("<id>https://ducks.fs-info.de/</id>");
echo("<updated>" . date("c", strtotime($postings[0]["timestamp"])) . "</updated>");
foreach($postings as $posting) {
    $duck = getDuck($posting["duck"]);
    echo("<entry>");
    echo("<id>https://ducks.fs-info.de/" . $duck["id"] . "#posting-" . $posting["id"] . "</id>");
    echo("<link href=\"https://ducks.fs-info.de/" . $duck["id"] . "#posting-" . $posting["id"] . "\" rel=\"alternate\" />");
    echo("<title>Tagebucheintrag für " . $duck["displayName"] . " von " . htmlspecialchars($posting["author"]) . "</title>");
    echo("<author><name>" . htmlspecialchars($posting["author"]) . "</name></author>");
    echo("<content>" . htmlspecialchars($posting["content"]) . "</content>");
    echo("<updated>" . date("c", strtotime($posting["timestamp"])) . "</updated>");
    echo("<category term=\"" . $duck["displayName"] . "\" />");
    echo("</entry>");
}
echo("</feed>");