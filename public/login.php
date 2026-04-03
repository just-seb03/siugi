<?php
session_start();

if (!empty($_SESSION['userId'])) {
    header("Location: index");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../src/login.php'; 
    
    $loginClass = new Login();

    if (isset($_POST['action']) && $_POST['action'] === 'get_name') {
        echo $loginClass->getNombreUsuario($_POST['username']);
        exit();
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($loginClass->authenticate($username, $password)) {
        echo "success";
    } else {
        echo "failure";
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIUGI</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/SIUGI/public/assets/css/plugins/all.min.css">
    <link rel="stylesheet" href="/SIUGI/public/assets/css/plugins/adminlte.min.css?v=3.2.0">

    <style>
        :root {
            --bg-body: #fdfbf6; 
            --bg-card: #ffffff;
            --text-main: #334155;
            --text-muted: #64748b;
            --primary: #475569; 
            --primary-light: #f1f5f9;
            --border: #e2e8f0;
            --radius-md: 16px; 
            --shadow-lg: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.login-page { background-color: var(--bg-body); font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-up { opacity: 0; animation: fadeInUp 0.6s ease-out forwards; }
        .login-box { width: 100%; max-width: 400px; padding: 15px; }
        
        .card-custom { background: var(--bg-card); border-radius: var(--radius-md); box-shadow: var(--shadow-lg); border: 1px solid var(--border); overflow: hidden; }
        .logo-container { padding: 2.5rem 1.5rem 1.5rem; text-align: center; background-color: #fcfcf9; border-bottom: 1px dashed var(--border); }
        .logo-container img { max-width: 180px; height: auto; margin-bottom: 1rem; transition: var(--transition); }
        .logo-container h4 { margin: 0; font-weight: 800; color: var(--primary); font-size: 1.2rem; letter-spacing: 1px; }

        .form-container { padding: 2rem 2.5rem 2.5rem; }
        .input-group-custom { display: flex; flex-direction: column; width: 100%; margin-bottom: 1.5rem; position: relative; }
        .input-group-custom label { font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px; }
        .input-group-custom .icon-wrapper { position: absolute; bottom: 0; left: 0; height: 42px; width: 42px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); transition: var(--transition); }
        .input-group-custom input { padding: 10px 14px 10px 42px; border-radius: 8px; border: 1px solid var(--border); background: #fff; color: var(--text-main); font-size: 0.95rem; font-family: 'Inter', sans-serif; transition: var(--transition); width: 100%; height: 42px; box-sizing: border-box; }
        .input-group-custom input:focus { border-color: #cbd5e1; outline: none; box-shadow: 0 0 0 3px var(--primary-light); }
        .input-group-custom input:focus + .icon-wrapper { color: var(--primary); }

        .btn-submit-custom { background: var(--primary); color: white; padding: 12px; border-radius: 8px; border: none; font-weight: 800; font-size: 0.95rem; cursor: pointer; transition: var(--transition); text-transform: uppercase; letter-spacing: 1px; width: 100%; display: flex; justify-content: center; align-items: center; gap: 10px; box-shadow: 0 4px 10px rgba(71, 85, 105, 0.2); margin-top: 1rem; }
        .btn-submit-custom:hover { background: #334155; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(71, 85, 105, 0.3); }
        .welcome-text { text-align: center; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem; transition: var(--transition); }

        .dynamic-avatar-container { max-height: 0; opacity: 0; overflow: hidden; display: flex; flex-direction: column; align-items: center; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); margin-bottom: 0; }
        .dynamic-avatar-container.show { max-height: 150px; opacity: 1; margin-bottom: 1.5rem; }
        .dynamic-avatar-wrapper { width: 76px; height: 76px; border-radius: 50%; overflow: hidden; border: 2px solid var(--border); box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 8px; display: flex; align-items: center; justify-content: center; background-color: var(--primary-light); }
        .dynamic-avatar-wrapper img { width: 100%; height: 100%; object-fit: cover; object-position: center; transform: scale(1.3); }
        .dynamic-avatar-name { font-size: 0.85rem; font-weight: 800; color: var(--primary); text-transform: capitalize; letter-spacing: 0.5px; }
    </style>
</head>

<body class="hold-transition login-page">

    <div class="login-box animate-up">
        <div class="card-custom">
            
            <div class="logo-container">
                <img src="/SIUGI/public/assets/images/logo_fiscalia.png" alt="Logo Fiscalía" onerror="this.src='/SIUGI/public/assets/images/logo_200_b.png'">
                <h4>SIUGI</h4>
            </div>

            <div class="form-container">
                <p class="welcome-text" id="welcomeText">Ingrese sus credenciales para acceder</p>

                <div class="dynamic-avatar-container" id="avatarContainer">
                    <div class="dynamic-avatar-wrapper">
                        <img id="userImg" src="" alt="Avatar de usuario">
                    </div>
                    <div class="dynamic-avatar-name" id="avatarName"></div>
                </div>

                <form id="loginForm" action="login" method="POST">
                    
                    <div class="input-group-custom">
                        <label for="username">Usuario</label>
                        <input type="text" id="username" name="username" required autocomplete="off">
                        <div class="icon-wrapper"><i class="fas fa-user"></i></div>
                    </div>
                    
                    <div class="input-group-custom">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" required>
                        <div class="icon-wrapper"><i class="fas fa-lock"></i></div>
                    </div>
                    
                    <button type="submit" class="btn-submit-custom">
                        Iniciar Sesión <i class="fas fa-sign-in-alt ml-1"></i>
                    </button>

                </form>
            </div>

        </div>
    </div>

    <script src="/SIUGI/public/assets/js/plugins/jquery.min.js"></script>
    <script src="/SIUGI/public/assets/js/plugins/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#username').focus();
            
            const usernameInput = document.getElementById('username');
            const avatarContainer = document.getElementById('avatarContainer');
            const userImg = document.getElementById('userImg');
            const avatarName = document.getElementById('avatarName');
            const welcomeText = document.getElementById('welcomeText');
            const loginForm = document.getElementById('loginForm');

            usernameInput.addEventListener('input', async function() {
                const user = this.value.trim().toLowerCase();
                
                if (user.length > 2) { 
                    try {
                        const formData = new URLSearchParams();
                        formData.append('action', 'get_name');
                        formData.append('username', user);

                        const response = await fetch('login', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const nombreReal = await response.text();

                        if (nombreReal.trim() !== '') {
                            const fotoUrl = `/SIUGI/public/avatar/${user}.jpg`;
                            const tester = new Image();
                            tester.src = fotoUrl;
                            
                            tester.onload = function() {
                                userImg.src = fotoUrl;
                                avatarName.textContent = nombreReal; 
                                
                                avatarContainer.classList.add('show');
                                welcomeText.style.display = 'none';
                            };
                            
                            tester.onerror = function() {
                                avatarContainer.classList.remove('show');
                                welcomeText.style.display = 'block';
                            };

                        } else {
                            avatarContainer.classList.remove('show');
                            welcomeText.style.display = 'block';
                        }

                    } catch (error) {
                        console.error(error);
                    }

                } else {
                    avatarContainer.classList.remove('show');
                    welcomeText.style.display = 'block';
                }
            });

            loginForm.addEventListener('submit', async function(e) {
                e.preventDefault(); 
                
                const btnSubmit = document.querySelector('.btn-submit-custom');
                const textoOriginal = btnSubmit.innerHTML;
                btnSubmit.innerHTML = 'Verificando... <i class="fas fa-spinner fa-spin ml-1"></i>';
                btnSubmit.disabled = true;

                try {
                    const formData = new FormData(this);
                    
                    const response = await fetch('login', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.text();

                    if (result.trim() === 'success') {
                        window.location.href = 'index';
                    } else {
                        welcomeText.innerHTML = '<span style="color: #ef4444; font-weight: bold;">Usuario o contraseña incorrectos.</span>';
                        welcomeText.style.display = 'block';
                        
                        btnSubmit.innerHTML = textoOriginal;
                        btnSubmit.disabled = false;
                    }
                } catch (error) {
                    welcomeText.innerHTML = '<span style="color: #ef4444;">Error de conexión.</span>';
                    btnSubmit.innerHTML = textoOriginal;
                    btnSubmit.disabled = false;
                }
            });
        });
    </script>
</body>
</html>