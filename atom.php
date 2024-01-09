<?php
global $db;
require "lib.php";
header("Content-Type: application/atom+xml");
echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n");
echo("<feed xmlns=\"http://www.w3.org/2005/Atom\">\n");
echo("  <title>Enten Tagebücher</title>\n");
echo("  <link href=\"https://ducks.fs-info.de/\" rel=\"alternate\" />\n");
echo("  <link href=\"https://ducks.fs-info.de/atom.php\" rel=\"self\" />\n");
echo("  <id>https://ducks.fs-info.de/</id>\n");
echo("  <updated>" . date("c", strtotime($db->query("SELECT timestamp FROM diary ORDER BY timestamp DESC LIMIT 1;")["timestamp"])) . "</updated>\n");
foreach($db->queryAll("SELECT * FROM ducks ORDER BY name ASC;") as $duck) {
    $duckName = $duck["name"];
    foreach ($db->queryAll("SELECT id, timestamp, author, entry FROM diary WHERE duck=? ORDER BY timestamp DESC;", $duckName) as $entry) {
        echo("  <entry>\n");
        echo("      <id>https://ducks.fs-info.de/" . str_replace(" ", "_", strtolower($duckName)) . "#" . $entry["id"] . "</id>\n");
        echo("      <link href=\"https://ducks.fs-info.de/" . str_replace(" ", "_", strtolower($duckName)) . "#" . $entry["id"] . "\" rel=\"alternate\" />\n");
        echo("      <title>Tagebucheintrag für $duckName von " . $entry["author"] . "</title>\n");
        echo("      <author><name>" . $entry["author"] . "</name></author>\n");
        echo("      <content>" . $entry["entry"] . "</content>\n");
        echo("      <updated>" . date("c", strtotime($entry["timestamp"])) . "</updated>\n");
        echo("      <category term=\"$duckName\" />\n");
        echo("  </entry>\n");
    }
}
echo("</feed>\n");
?>