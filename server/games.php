<?php

// Output all PUBLIC games in the format of a JSON

require_once('database/connect.php');

$database = getDatabase();

// Get limit and offset from query parameters, with default values
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

// Ensure limit does not exceed 20
$limit = min($limit, 20);

$statement = $database->prepare('SELECT g_id, author, title, g_swf, date, user_id FROM games WHERE ispublished = 1 AND isdeleted = 0 AND isprivate = 0 LIMIT :limit OFFSET :offset');
$statement->bindValue(':limit', $limit, PDO::PARAM_INT);
$statement->bindValue(':offset', $offset, PDO::PARAM_INT);
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result);