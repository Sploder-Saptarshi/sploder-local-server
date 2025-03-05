<?php

session_id($_GET['PHPSESSID']);
session_start();

if (isset($_SESSION['username'])) {
    require_once('../../database/connect.php');

    echo "keepalive=1";
} else {
    echo "keepalive=0";
}