<?php

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function getLoggedInUserId() {
    return $_SESSION['user_id'] ?? null;
}

function logout() {
    session_destroy();
    header("Location: login.php");
    exit();
}

?>