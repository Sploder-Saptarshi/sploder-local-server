<?php

require_once('../database/connect.php');
session_id($_GET['PHPSESSID']);
session_start();

if (!isset($_SESSION['userid']) || !isset($_GET['gameid'])) {
    echo "false";
    exit();
}

$userId = $_SESSION['userid'];
$gameId = (int)$_GET['gameid'];

$database = getDatabase();

// Verify that the game belongs to the user
$statement = $database->prepare('SELECT COUNT(*) FROM games WHERE g_id = :gameid AND user_id = :userid');
$statement->bindValue(':gameid', $gameId, PDO::PARAM_INT);
$statement->bindValue(':userid', $userId, PDO::PARAM_INT);
$statement->execute();
$count = $statement->fetchColumn();

if ($count == 0) {
    echo "false";
    exit();
}

// Delete the game
$statement = $database->prepare('DELETE FROM games WHERE g_id = :gameid AND user_id = :userid');
$statement->bindValue(':gameid', $gameId, PDO::PARAM_INT);
$statement->bindValue(':userid', $userId, PDO::PARAM_INT);
$success = $statement->execute();

if ($success) {
    echo "true";
} else {
    echo "false";
}