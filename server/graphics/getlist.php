<?php

header('Content-Type: application/xml');
session_id($_GET['PHPSESSID']);
session_start();
$start = isset($_GET['start']) ? (int)$_GET['start'] : 0; // Offset from this value
$num = isset($_GET['num']) ? (int)$_GET['num'] : 20; // LIMIT to this value

require('../database/connect.php');

$db = getDatabase();

$clause = "ispublished=1 AND isprivate=0";
$order = "id";
$extrainfo = "";
$params = [
    ':num' => $num,
    ':start' => $start
];

if (isset($_GET['userid']) && $_GET['userid'] == $_SESSION['userid']) {
    $clause = isset($_GET['published']) ? "ispublished=1" : "1=1";
    $params[':userid'] = $_SESSION['userid'];
    $clause .= " AND userid=:userid";
    $order = "id";
} elseif (isset($_GET['userid']) && $_GET['userid'] == 0) {
    if (isset($_GET['searchmode'])) {
        $searchmode = $_GET['searchmode'];
        $searchterm = $_GET['searchterm'];
        if ($searchmode == "users") {
            // Search by username according to corresponding userid
            $qs = "SELECT userid FROM members WHERE username=:username";
            $stmt = $db->prepare($qs);
            $stmt->bindValue(':username', $searchterm, PDO::PARAM_STR);
            $stmt->execute();
            $userid = $stmt->fetchColumn();
            $clause .= " AND userid=:userid";
            $params[':userid'] = $userid;
        } else {
            // Search by tags
            $qs = "SELECT g_id FROM graphic_tags WHERE tag LIKE :tag";
            $stmt = $db->prepare($qs);
            $stmt->bindValue(':tag', '%' . $searchterm . '%', PDO::PARAM_STR);
            $stmt->execute();
            $g_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            // Remove duplicate keys in g_ids
            $g_ids = array_unique($g_ids);
            if (!empty($g_ids)) {
                $g_ids = array_values($g_ids); // Extract values from associative array
                $clause .= " AND id IN (" . implode(",", array_map('intval', $g_ids)) . ")";
                $extrainfo .= ", (SELECT username FROM members WHERE userid=graphics.userid) AS username";
            } else {
                $clause .= " AND 1=0"; // No results found
            }
        }
    } else {
        $extrainfo .= ", (SELECT username FROM members WHERE userid=graphics.userid) AS username";
    }
}

$graphics_qs = "SELECT id, version $extrainfo FROM graphics WHERE $clause ORDER BY $order DESC LIMIT :num OFFSET :start";
$stmt = $db->prepare($graphics_qs);
$stmt->bindValue(':num', $num, PDO::PARAM_INT);
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
foreach ($params as $key => $value) {
    if ($key !== ':num' && $key !== ':start') {
        $stmt->bindValue($key, $value, PDO::PARAM_INT);
    }
}
$stmt->execute();
$graphicsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$graphicsData = array_map(function ($graphic) {
    $filteredGraphic = array_filter($graphic, function ($key) {
        return !is_numeric($key);
    }, ARRAY_FILTER_USE_KEY);
    return $filteredGraphic;
}, $graphicsData);

unset($params[':num']);
unset($params[':start']);
$total_qs = "SELECT COUNT(*) FROM graphics WHERE $clause";
$stmt = $db->prepare($total_qs);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_INT);
}
$stmt->execute();
$total = $stmt->fetchColumn();

$xml = new SimpleXMLElement('<graphics/>');
$xml->addAttribute('start', $start);
$xml->addAttribute('num', $num);
$xml->addAttribute('total', $total);

foreach ($graphicsData as $graphic) {
    $g = $xml->addChild('g');
    foreach ($graphic as $key => $value) {
        $g->addAttribute($key, $value);
    }
}

echo $xml->asXML();