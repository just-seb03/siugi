<?php 
class DatabaseConnection {
    private $pdo_info;
    private $pdo_cuentas;
    private $pdo_inv;

    public function __construct() {
        $host = 'localhost';
        $charset = 'utf8mb4';
        
        $user = 'ugi4';
        $pass = 'inffr4';

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo_info = new PDO("mysql:host=$host;dbname=informatica;charset=$charset", $user, $pass, $options);
            $this->pdo_cuentas = new PDO("mysql:host=$host;dbname=gestor_cuentas;charset=$charset", $user, $pass, $options);
            $this->pdo_inv = new PDO("mysql:host=$host;dbname=gestor_inventario;charset=$charset", $user, $pass, $options);
        } catch (\PDOException $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }


    public function getMysqlConnection() { 
        return $this->pdo_info; 
    }
    

    public function getInfoConnection() { 
        return $this->pdo_info; 
    }
    

    public function getCuentasConnection() { 
        return $this->pdo_cuentas; 
    }
    

    public function getInvConnection() { 
        return $this->pdo_inv; 
    }
}
?>