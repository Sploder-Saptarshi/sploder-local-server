<?php
session_id($_GET['PHPSESSID']);
session_start();
require_once(__DIR__ . '/../../config/env.php');
if (!isset($_GET['url'])) {
    die('No target URL set.');
}

$url = $_GET['url'];
$domain = getenv('DOMAIN_NAME');

// Parse the URL to separate the path and query parameters
$parsed_url = parse_url($url);
$path = $parsed_url['path'];
$query = isset($parsed_url['query']) ? $parsed_url['query'] : '';

// Whitelist specific paths
$whitelisted_paths = [
    '/php/getproject.php',
    '/php/getprojects.php',
    '/php/saveproject7.php',
    '/php/savegamedata7.php'
];

if (!in_array($path, $whitelisted_paths)) {
    // Check whether URL is in the format of
    // /users/userx/images/projx/thumbnail.png
    // using regex
    if (!preg_match('/images\/[^\/]+\/thumbnail\.png$/', $path)) {
        die('URL not whitelisted.');
    } else {
        $path = '/users/user' . $_SESSION['userid'] . '/' . $path;
    }
}

// Merge the query parameters
$get_data = $_GET;
unset($get_data['url']); // Remove 'url' parameter to avoid duplication
if ($query) {
    parse_str($query, $query_params);
    $get_data = array_merge($get_data, $query_params);
}

// Set up the $_GET and $_POST superglobals for the included script
$_GET = $get_data;
$_POST = $_POST;

// Debugging: Log the full URL
error_log("Full URL: " . $domain . $path);

// Include and execute the target PHP script
$script_path = $_SERVER['DOCUMENT_ROOT'] . $path;
if (file_exists($script_path)) {
    include($script_path);
} else {
    die('File not found: ' . $script_path);
}