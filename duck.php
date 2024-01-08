<?php

global $db, $isAdmin, $isAuthorized;
require "lib.php";

$duckName = substr($_SERVER["REQUEST_URI"], 1);
$duckNameParamStart = strpos($duckName, "?");
if ($duckNameParamStart)
    $duckName = substr($duckName, 0, $duckNameParamStart);
$duckName = str_replace("_", " ", $duckName);
$duck = $db->query("SELECT * FROM ducks WHERE name=?;", $duckName);
if ($duck == null) {
    header("HTTP/1.1 404 Duck not found");
    exit;
}
$duckName = $duck["name"];
global $isAdmin;
$isAuthorized = $isAdmin || (isset($_GET["pwd"]) && $_GET["pwd"] == $duck["password"]);
$order = isset($_GET["order"]) && strtolower($_GET["order"]) == "latest-last" ? "latest-last" : "latest-first";
$year = $_GET["year"] ?? date("Y");
$month = $_GET["month"] ?? date("n");

?>
<!doctype html>
<html lang="de">

<head>

    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>FSinfo Enten</title>
    <meta name="author" content="Fabian Dietrich">
    <link rel="stylesheet" href="style.css">

</head>

<body>

<main>

    <?php echo("<h1>$duckName</h1><img alt=\"$duckName\" src=\"" . (file_exists("img/$duckName.png") ? "img/$duckName.png" : "img/duck.png") . "\">"); ?>

    <form method="get">
        <?php if (isset($_GET["pwd"])) echo("<input type=\"hidden\" name=\"pwd\" value=\"" . $_GET["pwd"] . "\">"); ?>
        <?php if (isset($_GET["order"])) echo("<input type=\"hidden\" name=\"order\" value=\"" . $_GET["order"] . "\">"); ?>
        <div class="form-group">
            <label for="year">Jahr: </label>
            <select id="year" name="year">
                <?php for ($i = 2023; $i <= date("Y"); $i++) echo("<option" . ($i == $year ? " selected" : "") . ">$i</option>"); ?>
            </select>
        </div>
        <div class="form-group">
            <label for="month">Monat: </label>
            <select id="month" name="month">
                <?php for ($i = 1; $i <= 12; $i++) echo("<option" . ($i == $month ? " selected" : "") . ">$i</option>"); ?>
            </select>
        </div>
        <div class="form-group">
            <label for="order">Reihenfolge: </label>
            <select id="order" name="order">
                <option value="latest-first">Neuste zuerst</option>
                <option value="latest-last"<?php if($order == "latest-last") echo(" selected"); ?>>Neuste zuletzt</option>
            </select>
        </div>
        <div class="form-group">
            <button type="submit">Anzeigen</button>
        </div>
    </form>

    <?php

    if ($isAuthorized && isset($_POST["submit"]) && isset($_POST["author"]) && isset($_POST["entry"])) {
        $db->exec("INSERT INTO diary (`duck`, `timestamp`, `author`, `entry`) VALUES (?, CURRENT_TIMESTAMP(), ?, ?);", $duckName, htmlspecialchars($_POST["author"]), htmlspecialchars($_POST["entry"]));
        echo("<div class=\"success-message\">Deine Geschichte wurde gespeichert.</div>");
    } else if ($isAdmin && isset($_POST["delete"]) && isset($_POST["id"])) {
        $db->exec("DELETE FROM diary WHERE id=?;", $_POST["id"]);
        echo("<div class=\"success-message\">Die Geschichte wurde gelöscht.</div>");
    }

    function inputForm(): void
    { ?>
        <form method="post" id="diary-entry-submission">
            <div class="form-group">
                <label for="author">Dein Name:</label>
                <input type="text" id="author" name="author" placeholder="Quietschie" required>
            </div>
            <div class="form-group">
                <label for="entry">Erzähle deine Geschichte mit <?php global $duckName;
                    echo $duckName; ?>:</label>
                <textarea id="entry" name="entry" rows="5"></textarea>
            </div>
            <div class="form-group">
                <button type="submit" name="submit">Absenden</button>
            </div>
        </form>
    <?php }

    if ($isAuthorized && $order == "latest-first") {
        inputForm();
    }

    foreach ($db->queryAll("SELECT id, YEAR(timestamp) AS year, MONTH(timestamp) AS month, DAY(timestamp) AS day, HOUR(timestamp) AS hour, MINUTE(timestamp) AS minute, author, entry FROM diary WHERE duck=? AND YEAR(timestamp)=? AND MONTH(timestamp)=? ORDER BY timestamp " . ($order == "latest-first" ? "DESC" : "ASC") . ";", $duckName, $year, $month) as $entry) {
        $date = zeroPad($entry["day"]) . "." . zeroPad($entry["month"]) . "." . zeroPad($entry["year"]);
        $time = zeroPad($entry["hour"]) . ":" . zeroPad($entry["minute"]); ?>
        <div id="<?php echo($entry["id"]); ?>" class="diary-entry">
            <div class="meta">
                <?php echo($entry["author"] . " schrieb am $date um $time:"); ?>
                <?php if($isAdmin) { ?>
                <form method="post" style="float: right;">
                    <input type="hidden" name="id" value="<?php echo($entry["id"]); ?>">
                    <button type="submit" name="delete">Löschen</button>
                </form>
                <?php } ?>
            </div>
            <div class="content"><?php echo $entry["entry"]; ?></div>
        </div>
    <?php }

    if ($isAuthorized && $order == "latest-last") {
        inputForm();
    }

    ?>

</main>

</body>

</html>