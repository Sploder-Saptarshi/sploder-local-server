<?php
require_once('database/connect.php');
$username = trim($_POST['username']);
$password = $_POST['password'];

if (empty($username) || empty($password)) {
    echo "false";
    return;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$database = getDatabase();

// Add bootstrapping if the table does not exist
// Use a cache file to prevent multiple bootstrapping
if(!file_exists('.db_created')) {
    bootstrapDatabase($database);
}
function bootstrapDatabase($database) {
    $database->exec('CREATE TABLE IF NOT EXISTS users (userid INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE, password TEXT)');
    $createTableQuery = '
    CREATE TABLE IF NOT EXISTS games (
        g_id INTEGER PRIMARY KEY AUTOINCREMENT,
        author TEXT,
        user_id INTEGER,
        title TEXT,
        date TEXT,
        description TEXT,
        g_swf INTEGER,
        ispublished INTEGER,
        isdeleted INTEGER,
        isprivate INTEGER,
        comments INTEGER
    )
';

    $database->exec($createTableQuery);
    // Create a table for storing graphic
    // $qs = "INSERT INTO graphics (version, userid, isprivate, ispublished) VALUES (0, :userid, true, false) RETURNING id";
    $createTableQuery = '
    CREATE TABLE IF NOT EXISTS graphics (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        version INTEGER,
        userid INTEGER,
        isprivate INTEGER,
        ispublished INTEGER
    )
';
    $database->exec($createTableQuery);

    // Now cache that the table has been made
    file_put_contents('.db_created', 'true');
}

$statement = $database->prepare('INSERT INTO users (username, password) VALUES (TRIM(:username), :password)');
$statement->bindValue(':username', $username, SQLITE3_TEXT);
$statement->bindValue(':password', $hash, SQLITE3_TEXT);
$statement->execute();

echo "true";