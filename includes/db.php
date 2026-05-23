<?php

function getDB() {

    static $db = null;

    if ($db === null) {

        $db = new mysqli(
            'localhost',
            'root',
            '',
            'ap_db'
        );

        if ($db->connect_error) {
            die('Database Connection Failed: ' . $db->connect_error);
        }

        $db->set_charset('utf8mb4');
    }

    return $db;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}