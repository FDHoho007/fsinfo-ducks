<?php

define("NTFY_BASE_URL", getenv("NTFY_BASE_URL"));
define("NTFY_NAMESPACE", getenv("NTFY_NAMESPACE"));
define("NTFY_TOPIC_ALL", getenv("NTFY_TOPIC_ALL"));
define("NTFY_USERNAME", getenv("NTFY_USERNAME"));
define("NTFY_PASSWORD", getenv("NTFY_PASSWORD"));
define("DEFAULT_DUCK_PICTURE", getenv("DEFAULT_DUCK_PICTURE"));
define("SESSION_TIME", intval(getenv("SESSION_TIME")));
define("POST_EDIT_TIMEFRAME", intval(getenv("POST_EDIT_TIMEFRAME")));
define("BIOGRAPHY_MAX_LENGTH", intval(getenv("BIOGRAPHY_MAX_LENGTH")));
define("POSTING_MAX_LENGTH", intval(getenv("POSTING_MAX_LENGTH")));
define("AUTHOR_MAX_LENGTH", intval(getenv("AUTHOR_MAX_LENGTH")));

setlocale(LC_TIME, "de_DE.UTF-8");
require_once "util.php";
require_once "mysql.php";
global $db;
$db = new MySQLConnection("mysql:host=" . getenv("MYSQL_HOST") . ";dbname=" . getenv("MYSQL_DATABASE"), getenv("MYSQL_USER"), getenv("MYSQL_PASSWORD"));
require_once "includes/duck.php";
require_once "postings.php";
require_once "audit.php";
require_once "session.php";