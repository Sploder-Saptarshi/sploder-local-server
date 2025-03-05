<?php

class GameData {
    public $author;
    public $difficulty;
    public $avgScore;

    public function __construct($author, $difficulty, $avgScore) {
        $this->author = $author;
        $this->difficulty = $difficulty;
        $this->avgScore = $avgScore;
    }
}

function getGameData($db, int $gameId): GameData
{
    $stmt = $db->prepare("SELECT author FROM games WHERE g_id = :g_id");
    $stmt->execute([':g_id' => $gameId]);
    $gameInfo = $stmt->fetch(PDO::FETCH_ASSOC);

 
        $avgScore = 3;

    return new GameData($gameInfo['author'], 5, $avgScore);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if(!str_contains($_GET['g'], "_")){
    $username = $_SESSION['username'] ?? "DEMO";
    die();
}

$separated = explode("_", $_GET['g']);
$userId = $separated[0];
$gameId = $separated[1];

require_once('../database/connect.php');
$db = getDatabase();

$gameData = getGameData($db, $gameId);

echo "&username={$gameData->author}&difficulty=" . $gameData->difficulty . "&rating={$gameData->avgScore}";