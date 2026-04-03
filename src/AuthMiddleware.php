<?php
function verificarSesion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['userId'])) {
        header("Location: /login.php");
        exit();
    }
}