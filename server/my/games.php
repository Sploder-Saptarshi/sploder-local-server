<?php

require_once('../database/connect.php');
session_id($_GET['PHPSESSID']);
session_start();

if (!isset($_SESSION['userid'])) {
    echo json_encode([]);
    exit();
}

$userId = $_SESSION['userid'];
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10; // Default limit to 10
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0; // Default offset to 0

$database = getDatabase();

$statement = $database->prepare('SELECT g_id, title, date, user_id, g_swf, ispublished FROM games WHERE user_id = :userid LIMIT :limit OFFSET :offset');
$statement->bindValue(':userid', $userId, PDO::PARAM_INT);
$statement->bindValue(':limit', $limit, PDO::PARAM_INT);
$statement->bindValue(':offset', $offset, PDO::PARAM_INT);
$statement->execute();
$games = $statement->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($games);