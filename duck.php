<?php

require_once "includes/autoload.php";

$duckId = substr($_SERVER["REQUEST_URI"], 1);
if ($duckIdParamStart = strpos($duckId, "?"))
    $duckId = substr($duckId, 0, $duckIdParamStart);
$duck = getDuck($duckId);
if($duck == null) {
    http_response_code(404);
    echo("Duck not found");
    exit;
}

?>
<!doctype html>
<html lang="de">

<head>

    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="noindex,nofollow">
    <title>FSinfo Enten</title>
    <meta name="author" content="Fabian Dietrich">
    <meta name="description"
          content="Hier findest du die Geschichten, der Enten der Fachschaft Info der Universität Passau.">
    <link rel="stylesheet" href="/assets/style.css">
    <link rel="alternate" type="application/atom+xml" href="/atom.php?duck=<?= $duckId ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/api.js"></script>
    <script src="assets/js/session.js"></script>
    <script src="assets/js/postings.js"></script>
    <script src="assets/js/duck.js"></script>

    <script>
        const CSRF_TOKEN="<?= $_SESSION["csrf_token"] ?>";
        const DUCK="<?= $duck["id"] ?>";
        const BIOGRAPHY_MAX_LENGTH=<?= BIOGRAPHY_MAX_LENGTH ?>;
        const POSTING_MAX_LENGTH=<?= POSTING_MAX_LENGTH ?>;
        const AUTHOR_MAX_LENGTH=<?= AUTHOR_MAX_LENGTH ?>;
    </script>

    <style>#main-header h1 { margin-bottom: 5px; }</style>

</head>

<body>

<div id="page-wrapper">
    <header>
        <button id="menu-toggle">☰</button>
        <a id="page-title" href="/"><img id="logo-img" alt="FSinfo" src="/assets/fsinfo.png"> Enten</a>
        <div id="user-options">
            <img id="user-menu-toggle" class="profile-picture-small" alt="Account" src="/uploads/<?php echo(DEFAULT_DUCK_PICTURE); ?>.png">
            <ul id="user-menu">
                <?php if(isAdmin()): ?>
                <li><a>als Admin angemeldet</a></li>
                <?php else: if(isModerator()): ?>
                <li><a>als Mod angemeldet</a></li>
                <?php else: ?>
                <li><a>nicht angemeldet</a></li>
                <?php endif; endif; ?>
                <li><a href="#" onclick="showDuckLoginDialog();">Mit Ente anmelden</a></li>
                <li><a href="#" onclick="showKeyDialog();">Berechtigungsschlüssel verwenden</a></li>
                <?php if(isAdmin() || isModerator()): ?>
                <li><a href="#" onclick="showLogoutDialog();">Abmelden</a></li>
                <?php endif; ?>
            </ul>
        </div>

    </header>

    <nav>
        <ul>
        <?php foreach(getDucks() as $navDuck): ?>
            <li>
                <a href="/<?= $navDuck["id"] ?>">
                    <img class="profile-picture-small" alt="Profilbild" src="<?= $navDuck["picture"] ?>">
                    <?= $navDuck["displayName"] ?>
                </a>
            </li>
        <?php endforeach; ?>
        </ul>
    </nav>

    <main>
        <div id="main-header" data-duck-id="<?= $duck["id"] ?>">
            <img class="profile-picture-big" alt="<?= $duck["displayName"] ?>" src="<?= $duck["picture"] ?>">
            <!-- Edit Picture -->
            <h1><?= $duck["displayName"] ?></h1>
            <div id="duck-actions">
                <?php if(isAdmin()): ?>
                    <button id="unlock-duck-button" onclick="showDuckUnlockDialog(this.parentElement.parentElement);"<?php if(!$duck["locked"]) echo(" style=\"display: none;\""); ?>>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-lock" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2M5 8h6a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1"/></svg>
                    </button>
                    <button id="lock-duck-button" onclick="showDuckLockDialog(this.parentElement.parentElement);"<?php if($duck["locked"]) echo(" style=\"display: none;\""); ?>>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-unlock" viewBox="0 0 16 16"><path d="M11 1a2 2 0 0 0-2 2v4a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h5V3a3 3 0 0 1 6 0v4a.5.5 0 0 1-1 0V3a2 2 0 0 0-2-2M3 8a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1z"/></svg>
                    </button>
                <?php endif; ?>
                <button id="subscribe-duck-button" onclick="showNotificationDialog(this.parentElement.parentElement);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16"><path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"/></svg>
                </button>
                <a href="/atom.php?duck=<?= $duckId ?>" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-rss" viewBox="0 0 16 16"><path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/><path d="M5.5 12a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m-3-8.5a1 1 0 0 1 1-1c5.523 0 10 4.477 10 10a1 1 0 1 1-2 0 8 8 0 0 0-8-8 1 1 0 0 1-1-1m0 4a1 1 0 0 1 1-1 6 6 0 0 1 6 6 1 1 0 1 1-2 0 4 4 0 0 0-4-4 1 1 0 0 1-1-1"/></svg>
                </a>
            </div>
            <div id="duck-biography">
                <p>
                    <?= htmlspecialchars($duck["biography"] ?? "keine Biografie gesetzt") ?>
                </p>
            <?php if(isAdmin() || (isDuckedIn($duck["id"]) && isModerator())): ?>
                <button onclick="showBiographyEditDialog(this.parentElement.parentElement);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/></svg>
                </button>
            <?php endif; ?>
            </div>
            <?php if(isAdmin() || isDuckedIn($duck["id"])): ?>
            <form id="create-post" onsubmit="createPosting(this); return false;">
                <h3 style="margin-top: 0;">Neuen Beitrag verfassen</h3>
                <textarea name="content" placeholder="Heute hat <?= $duck["displayName"] ?> etwas ganz besonderes erlebt..." maxlength="<?= POSTING_MAX_LENGTH ?>" required></textarea>
                <input type="text" name="author" placeholder="Dein Name" maxlength="<?= AUTHOR_MAX_LENGTH ?>" required>
                <button type="submit">Abschicken</button>
            </form>
            <?php endif; ?>
        </div>
        <div id="postings-list"></div>
        <div id="postings-loader" class="lds-facebook"><div></div><div></div><div></div></div>
    </main>
    <div id="attachment-viewer" onclick="this.style.display='none'; document.body.classList.remove('no-scroll');">
        <div><img alt="Anhang" src=""></div>
    </div>
</div>

</body>

</html>