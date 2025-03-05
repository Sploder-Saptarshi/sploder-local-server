<?php

if ($_GET['other'] == '') {
    $file = file_get_contents('creator5.swf');
    // Output SWF header
    header('Content-Type: application/x-shockwave-flash');
    header('Content-Length: ' . strlen($file));
    echo $file;
} else {
    // Parse the 'other' parameter to extract the target URL
    $other_url = $_GET['other'];
    $parsed_url = parse_url($other_url);
    $path = $parsed_url['path'];
    $query = isset($parsed_url['query']) ? $parsed_url['query'] : '';

    // Ensure the path points to removehardcode.php
    if (basename($path) !== 'removehardcode.php') {
        die('Invalid target script.');
    }

    // Merge the query parameters
    $get_data = $_GET;
    unset($get_data['other']); // Remove 'other' parameter to avoid duplication
    if ($query) {
        parse_str($query, $query_params);
        $get_data = array_merge($get_data, $query_params);
    }

    // Set up the $_GET and $_POST superglobals for the included script
    $_GET = $get_data;
    $_POST = $_POST;

    // Debugging: Log the full URL
    error_log("Full URL: " . $other_url);

    // Include and execute the target PHP script
    $script_path = realpath(__DIR__ . '/../make/php/removehardcode.php');
    if (file_exists($script_path)) {
        include($script_path);
    } else {
        die('File not found: ' . $script_path);
    }
}