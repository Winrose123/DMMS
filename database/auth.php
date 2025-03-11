<?php
require_once 'connection.php';

function redirectLoggedInUser() {
    if (isset($_SESSION['user_id'])) {
        $redirect = $_SESSION['role'] === 'admin' ? 'dashboard.php' : 'farmer_dashboard.php';
        header('Location: ' . $redirect);
        exit();
    }
}

function getUserName() {
    return isset($_SESSION['name']) ? $_SESSION['name'] : '';
}

function getFarmerId() {
    return isset($_SESSION['farmer_id']) ? $_SESSION['farmer_id'] : null;
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isFarmer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'farmer';
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: access_denied.php');
        exit();
    }
}

function requireFarmer() {
    requireLogin();
    if (!isFarmer()) {
        header('Location: access_denied.php');
        exit();
    }
}
?>
