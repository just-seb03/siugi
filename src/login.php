<?php

class Login {
    private $pdo;

    public function __construct() {
        $database = new DatabaseConnection();
        $this->pdo = $database->getMysqlConnection();
    }

    public function authenticate($username, $password) {
        
        if ($password === "1") {
            
            $stmt = $this->pdo->prepare("
                SELECT id, usuario, nombre, cod_fiscalia, rut, cod_unidad, perfil 
                FROM usuarios 
                WHERE usuario = :usuario 
                AND usuario IN ('ningguang')
            ");
            
            $stmt->bindValue(':usuario', strtolower($username));
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {

                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                $_SESSION['userId']   = $user['id'];
                $_SESSION['user']     = $user['usuario'];
                $_SESSION['nombre']   = $user["nombre"];
                $_SESSION['fiscalia'] = $user['cod_fiscalia'];
                $_SESSION['rut']      = $user['rut'];
                $_SESSION['unidad']   = $user['cod_unidad'];
                $_SESSION['perfil']   = $user['perfil'];
                
                return true; 
            }
        }

        return false; 
    }

    public function getNombreUsuario($username) {
        $stmt = $this->pdo->prepare("
            SELECT nombre FROM usuarios 
            WHERE usuario = :usuario 
            AND usuario IN ('ningguang')
        ");
        $stmt->bindValue(':usuario', strtolower($username));
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user ? $user['nombre'] : ''; 
    }
}
?>