<?php
require_once('database/connect.php');
session_start();

$username = trim($_POST['username']);
$password = $_POST['password'];

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false]);
    return;
}
$database = getDatabase();

// Verify login
$statement = $database->prepare('SELECT password,userid FROM users WHERE username = :username');
$statement->bindValue(':username', $username, PDO::PARAM_STR);
$statement->execute();
$result = $statement->fetch(PDO::FETCH_ASSOC);

if ($result) {
    if (password_verify($password, $result['password'])) {
        $_SESSION['loggedIn'] = true;
        $_SESSION['PHPSESSID'] = session_id();
        $_SESSION['userid'] = $result['userid'];
        $_SESSION['username'] = $username;
        echo json_encode(['success' => true, 'sessionId' => session_id(), 'userid' => $_SESSION['userid']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid password']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}