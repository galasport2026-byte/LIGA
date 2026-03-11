<?php
require_once '../includes/config.php';

if (esta_logueado()) redirigir('dashboard.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario && $password) {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ? AND activo = 1 LIMIT 1");
        $stmt->execute([$usuario]);
        $admin = $stmt->fetch();

        // Verificar contraseña (bcrypt o texto plano como fallback temporal)
        $ok = false;
        if ($admin) {
            if (password_verify($password, $admin['password'])) {
                $ok = true;
            } elseif ($admin['password'] === $password) {
                // Fallback: contraseña en texto plano (migración)
                $ok = true;
            }
        }

        if ($ok) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_user'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['nombre'];
            // Actualizar último acceso
            $conn->prepare("UPDATE admins SET ultimo_acceso = NOW() WHERE id = ?")->execute([$admin['id']]);
            redirigir('dashboard.php');
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    } else {
        $error = 'Por favor completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin – Liga MFM</title>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Roboto',sans-serif;background:linear-gradient(135deg,#0a5f0a 0%,#1a7a1a 50%,#0a5f0a 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}
.card{background:#fff;padding:50px 44px;border-radius:22px;box-shadow:0 20px 60px rgba(0,0,0,.4);max-width:440px;width:100%;text-align:center;border:3px solid #d4af37;}
.logo{width:110px;height:110px;object-fit:contain;margin-bottom:18px;animation:float 3s ease-in-out infinite;}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
h1{font-family:'Oswald',sans-serif;font-size:2.4em;color:#0a5f0a;letter-spacing:3px;margin-bottom:6px;}
.subtitulo{color:#666;font-size:1em;margin-bottom:32px;}
.fg{text-align:left;margin-bottom:20px;}
.fg label{display:block;font-size:.82em;font-weight:700;color:#0a5f0a;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;}
.fg input{width:100%;padding:13px 16px;border:2px solid #e2e8f0;border-radius:10px;font-size:1em;font-family:'Roboto',sans-serif;transition:border .2s;}
.fg input:focus{outline:none;border-color:#0a5f0a;box-shadow:0 0 0 3px rgba(10,95,10,.1);}
.btn{width:100%;padding:15px;background:linear-gradient(135deg,#0a5f0a,#1a7a1a);color:#fff;border:none;border-radius:10px;font-family:'Oswald',sans-serif;font-size:1.1em;font-weight:700;letter-spacing:1px;cursor:pointer;transition:all .3s;margin-top:8px;}
.btn:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(10,95,10,.35);}
.error{background:#fef2f2;border:1px solid #fecaca;color:#be123c;padding:12px 16px;border-radius:8px;font-size:.9em;margin-top:16px;border-left:4px solid #e94560;}
.volver{display:block;margin-top:24px;color:#888;font-size:.85em;text-decoration:none;transition:color .2s;}
.volver:hover{color:#0a5f0a;}
</style>
</head>
<body>
<div class="card">
    <img src="../Logo MFM.png" alt="Logo" class="logo" onerror="this.style.display='none'">
    <h1>LIGA MFM</h1>
    <p class="subtitulo">Panel de Administración</p>

    <form method="POST" action="">
        <div class="fg">
            <label>👤 Usuario</label>
            <input type="text" name="username" placeholder="Ingresa tu usuario" required autofocus
                value="<?= limpiar($_POST['username'] ?? '') ?>">
        </div>
        <div class="fg">
            <label>🔐 Contraseña</label>
            <input type="password" name="password" placeholder="Ingresa tu contraseña" required>
        </div>
        <button type="submit" class="btn">Ingresar al Panel</button>
        <?php if ($error): ?>
            <div class="error">❌ <?= limpiar($error) ?></div>
        <?php endif; ?>
    </form>

    <a href="../index.php" class="volver">← Volver al sitio público</a>
</div>
</body>
</html>
