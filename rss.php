<?php
global $db;
require "lib.php";
header("Content-Type: text/xml");
echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
echo("<feed xmlns=\"http://www.w3.org/2005/Atom\">");
echo("<title>Enten Tagebücher</title>");
echo("<link href=\"https://ducks.fs-info.de/\" rel=\"alternate\" />");
echo("<id>https://ducks.fs-info.de/</id>");
echo("<updated>" . date("c", strtotime($db->query("SELECT timestamp FROM diary ORDER BY timestamp DESC LIMIT 1;")["timestamp"])) . "</updated>");
foreach($db->queryAll("SELECT * FROM ducks ORDER BY name ASC;") as $duck) {
    $duckName = $duck["name"];
    foreach ($db->queryAll("SELECT id, timestamp, author, entry FROM diary WHERE duck=? ORDER BY timestamp DESC;", $duckName) as $entry) {
        echo("<entry>");
        echo("<id>https://ducks.fs-info.de/" . str_replace(" ", "_", strtolower($duckName)) . "#" . $entry["id"] . "</id>");
        echo("<link href=\"https://ducks.fs-info.de/" . str_replace(" ", "_", strtolower($duckName)) . "#" . $entry["id"] . "\" rel=\"alternate\" \>");
        echo("<title>Tagebucheintrag für $duckName von " . $entry["author"] . "</title>");
        echo("<author><name>" . $entry["author"] . "</name></author>");
        echo("<content>" . $entry["entry"] . "</content>");
        echo("<updated>" . date("c", strtotime($entry["timestamp"])) . "</updated>");
        echo("<category term=\"$duckName\" />");
        echo("</entry>");
    }
}
echo("</feed>");
?>