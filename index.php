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

<h1 id="logo-heading"><img id="logo" src="img/logo.png"> Enten</h1>
<p>Hier findest du die Geschichten, der Enten der Fachschaft Info der Universität Passau.<br>
    Um die Geschichte einer Ente weiterzuschreiben, halte dein (NFC-fähiges) SmartPhone an ihr Halstuch.</p>

<?php

global $db, $isAdmin, $isAuthorized;
require "lib.php";

if ($isAdmin) {
    if (isset($_POST["name"])) {
        if (isset($_POST["create"])) {
            if ($db->query("SELECT * FROM ducks WHERE name=?;", $_POST["name"])) {
                echo("<div class=\"error-message\">\"" . $_POST["name"] . "\" ist bereits Teil der FSinfo.</div>");
            } else {
                $password = randomPassword();
                $db->exec("INSERT INTO ducks VALUES (?, ?);", $_POST["name"], $password);
                echo("<div class=\"success-message\">\"" . $_POST["name"] . "\" quietscht nun für die FSinfo. Ihr Passwort lautet: $password</div>");
            }
        } else if (isset($_POST["delete"])) {
            if ($db->query("SELECT * FROM ducks WHERE name=?;", $_POST["name"])) {
                $db->exec("DELETE FROM ducks WHERE name=?;", $_POST["name"]);
                echo("<div class=\"success-message\">Die Ente \"" . $_POST["name"] . "\" wurde in die Freiheit entlassen.</div>");
            } else {
                echo("<div class=\"error-message\">Es existiert in der FSinfo keine Ente mit diesem Namen.</div>");
            }
        }
    }
    ?>
    <form method="post">
        <input type="text" name="name" placeholder="Name der neuen Ente" required>
        <button type="submit" name="create">Ente hinzufügen</button>
    </form>
<?php }

echo("<div class=\"ducks\">");
foreach ($db->queryAll("SELECT * FROM ducks ORDER BY name ASC;") as $duck) {
    $duckName = $duck["name"];
    echo("<a class=\"duck\" href=\"/" . str_replace(" ", "_", strtolower($duckName)) . ($isAdmin ? "?pwd=" . $_GET["pwd"] : "") . "\">");
    echo("<img alt=\"$duckName\" src=\"" . (file_exists("ducks/$duckName.png") ? "ducks/$duckName.png" : "img/duck.png") . "\">$duckName");
    if($isAdmin) { ?>
            <br><br>
        <form method="post">
            <input type="hidden" name="name" value="<?php echo $duckName; ?>">
            <button type="submit" name="delete">Löschen</button>
        </form>
    <?php }
    echo("</a>");
}
echo("</div>");

?>

</main>

</body>

</html>
