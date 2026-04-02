<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /AguaHeart/admin/login.php');
        exit;
    }
}

function logout() {
    session_destroy();
    header('Location: /AguaHeart/admin/login.php');
    exit;
}
