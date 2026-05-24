<?php
session_start();
require_once './db.php'; 

// Si ya tiene sesión, mándalo al index, pero SOLO si no estamos procesando el POST
if (isset($_SESSION['user_id']) && $_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /contacto/index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$pdo) {
        $error = "ERROR DE CONEXIÓN.";
    } else {
        try {
            // MODIFICACIÓN: Validamos el email y el hash de la contraseña directamente en la consulta
            $stmt = $pdo->prepare("SELECT id, nombre FROM usuarios WHERE email = ? AND password = SHA2(?, 256)");
            $stmt->execute([$email, $password]);
            $user = $stmt->fetch();

            // Si la consulta nos devuelve un usuario, significa que las credenciales son 100% correctas
            if ($user) {
                // Sincronizamos variables de sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nombre'] = $user['nombre'];
                $_SESSION['user_email'] = $email; 
                
                header("Location: /contacto/index.php");
                exit;
            } else {
                $error = "CREDENCIALES INCORRECTAS.";
            }
        } catch (Exception $e) {
            $error = "ERROR DEL SISTEMA.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>LOGIN | LEXDEFENSOR</title>
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro:wght@400;800&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <a href="/" style="text-decoration: none;">
            <div class="logo">LEX<span>DEFENSOR</span></div>
        </a>
    </nav>
    <header class="page-header">
        <h1>ACCESO</h1>
        <p>Área restringida para clientes</p>
    </header>
    <section class="contact-section">
        <form class="contact-form" method="POST" action="login.php">
            <?php if ($error): ?>
                <p style="color: #d4af37; font-weight: bold; text-align: center; margin-bottom: 1rem;"><?php echo $error; ?></p>
            <?php endif; ?>
            <input type="email" name="email" placeholder="EMAIL DE USUARIO" required>
            <input type="password" name="password" placeholder="CONTRASEÑA" required>
            <button type="submit" class="btn-primary" style="border:none; cursor:pointer; width:100%;">INICIAR SESIÓN</button>
        </form>
    </section>
</body>
</html>