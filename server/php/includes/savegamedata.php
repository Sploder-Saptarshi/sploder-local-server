<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() == PHP_SESSION_NONE) {
    session_id($_GET['PHPSESSID']);
    session_start();
}
if (isset($_SESSION['PHPSESSID'])) { // session ID is valid and exists
    $xml = $_POST['xml'] ?? file_get_contents('php://input');
    $xml2 = simplexml_load_string(strval($xml)) or die("INVALID XML FILE!!");
    if(!isset($_GET['comments']) || !isset($_GET['private'])){
        die('<message result="failed" message="Please save your game again."/>');
    }
    $author = $_SESSION['username'];
    $comments = $_GET['comments'];
    $private = $_GET['private'];
    $ispublished = 1;
    $id = (int)filter_var($_GET['projid'], FILTER_SANITIZE_NUMBER_INT);

    require_once('../database/connect.php');
    $db = getDatabase();

    // Check if the user owns the game
    $stmt = $db->prepare("SELECT author FROM games WHERE g_id=:id");
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['author'] != $_SESSION['username']) {
        die('<message result="failed" message="You do not own this game!"/>');
    }
    $stmt = $db->prepare("UPDATE games
        SET ispublished=:ispublished, isprivate=:isprivate, comments=:comments
        WHERE g_id=:id");
    $stmt->execute([
        ':ispublished' => $ispublished,
        ':isprivate' => $private,
        ':comments' => $comments,
        ':id' => $id
    ]);
    $project_path = "../users/user" . $_SESSION['userid'] . "/projects/proj" . $id . "/";
    file_put_contents($project_path . "game.xml", $xml);
    echo '<message result="success" id="proj' . $id . '" pubkey="' . $_SESSION['userid'] . '_' . $id . '" message="Success"/>';
} else {
    echo '<message result="failed" message="The session ID is incorrect! Log out and log in again."/>';
}