<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

if (empty($_SESSION['userId'])) {
    header("Location: login");
    exit();
}

require_once __DIR__ . '/../views/inicio.php';