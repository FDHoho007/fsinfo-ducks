<?php require_once "includes/autoload.php"; ?>
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
    <link rel="alternate" type="application/atom+xml" href="/atom.php">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/api.js"></script>
    <script src="assets/js/session.js"></script>
    <script src="assets/js/postings.js"></script>

    <script>
        const CSRF_TOKEN="<?= $_SESSION["csrf_token"] ?>";
        const DUCK=null;
        const BIOGRAPHY_MAX_LENGTH=<?= BIOGRAPHY_MAX_LENGTH ?>;
        const POSTING_MAX_LENGTH=<?= POSTING_MAX_LENGTH ?>;
        const AUTHOR_MAX_LENGTH=<?= AUTHOR_MAX_LENGTH ?>;
    </script>

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
        <div id="main-header">
            <h1>FSinfo Enten</h1>
            <p>Hier findest du die Geschichten, der Enten der Fachschaft Info der Universität Passau. Melde dich als eine Ente an, um ihre Geschichte weiterzuschreiben.</p>
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