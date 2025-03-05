<?php

function getDatabase()
{
    // Use PDO
    $database = new PDO('sqlite:'.__DIR__.'/../database.db');
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $database;
}