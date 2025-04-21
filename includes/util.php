<?php

function randomPassword($len = 16): string
{
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $alphabetLength = strlen($alphabet) - 1;
    $pass = "";
    for ($i = 0; $i < $len; $i++) {
        $pass .= $alphabet[rand(0, $alphabetLength)];
    }
    return $pass;
}

function zeroPad(int $number): string
{
    if ($number < 10)
        return "0$number";
    return "$number";
}

function formatDate(string $datetime): string
{
    $ts = (new DateTime($datetime))->getTimestamp();
    return "am " . date("j.n.Y", $ts) . " um " . date("H:i", $ts);
}

function wasCurrentlyPosted(string $datetime): bool
{
    $ts = (new DateTime($datetime))->getTimestamp();
    return time() - $ts <= POST_EDIT_TIMEFRAME;
}

function renderPostings(array $postings): string
{
    $html = "";
    foreach ($postings as $posting) {
        $duck = getDuck($posting['duck']);
        $html .= "<div class='posting' id='posting-" . $posting["id"] . "'>";

        $html .= "<div class='posting-header'>";
        $html .= "<img class='profile-picture-small' alt='Profilbild' src='" . $duck["picture"] . "'>";
        $html .= "<div class='duck-name'>" . $duck["displayName"] . "</div>";
        if (isAdmin() || isDuckedIn($posting["duck"])) {
            $html .= "<div class='posting-action'>";
            if (isAdmin() || (isDuckedIn($posting["duck"]) && wasCurrentlyPosted($posting["timestamp"]) && $posting["authorIP"] == $_SERVER["REMOTE_ADDR"])) {
                $html .= "<button onclick='showPostingEditDialog(this.parentElement.parentElement.parentElement);'><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" viewBox=\"0 0 16 16\"><path d=\"M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325\"/></svg></button>";
            }
            if (isAdmin()) {
                $html .= "<button onclick='showPostingDeleteDialog(this.parentElement.parentElement.parentElement);'><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" viewBox=\"0 0 16 16\"><path d=\"M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5\"/></svg></button>";
            }
            $html .= "</div>";
        }
        $html .= "</div>";

        $html .= "<div class='posting-content'>" . htmlspecialchars($posting["content"]) . "</div>";

        $html .= "<div class='posting-attachments'>";
        foreach ($posting["attachments"] as $attachment) {
            if($attachment["fileType"] == "png") {
                $html .= "<a onclick='showAttachment(this);'><img alt='Bilddatei' src='/uploads/" . $attachment["id"] . ".png'></a>";
            }
        }
        $html .= "</div>";

        $html .= "<div class='posting-footer'>geschrieben von <span class='posting-author'";
        if (isAdmin()) {
            $html .= " title='" . $posting["authorIP"] . "'";
        }
        $html .= ">" . htmlspecialchars($posting["author"]) . "</span> " . formatDate($posting["timestamp"]) . "</div>";

        $html .= "</div>";
    }
    return $html;
}