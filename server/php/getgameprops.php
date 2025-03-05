<?php

$separated = array_map('intval', explode("_", urldecode($_GET['pubkey'])));
// Check whether pubkey matches game ID ad User ID
require_once('../database/connect.php');
$db = getDatabase();
$qs = "SELECT user_id FROM games WHERE g_id = :id";
$stmt = $db->prepare($qs);
$stmt->execute([':id' => $separated[1]]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);
if($separated[0] == $game['user_id']){
?>
&u=<?= $separated[0] ?>&c=0&m=<?= $separated[1] ?>&tv=0&a=0
<?php
} else {
    echo 'Error';
    die();
}