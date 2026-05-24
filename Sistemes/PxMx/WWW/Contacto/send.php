<?php
session_start();
require_once './db.php';

// Bloqueo de seguridad: Si no hay sesión, al login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$mensaje_final = "";
$es_error = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // RECOGER DATOS con salvavidas por si la sesión está corrupta
    $nombre  = $_SESSION['user_nombre'] ?? 'Usuario Desconocido'; 
    $email   = $_SESSION['user_email'] ?? 'correo_no_encontrado@lex.lan'; 
    
    $delito  = htmlspecialchars($_POST['delito'] ?? '');
    $mensaje = htmlspecialchars($_POST['mensaje'] ?? '');
    
    try {
        if (!$pdo) throw new Exception("El archivo db.php no pudo conectar con la BBDD.");

        // 1. Inserción en la base de datos
        $sql = "INSERT INTO citas (nombre, email, delito, mensaje) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $email, $delito, $mensaje]);

        // 2. Preparación y envío a Discord
        $fecha_actual = date("d-m-Y H:i:s");
        
        $cmd = sprintf(
            "/usr/local/bin/enviar_discord.sh %s %s %s %s %s > /dev/null 2>&1 &",
            escaphellarg($nombre), 
            escaphellarg($email), 
            escaphellarg($delito), 
            escaphellarg($mensaje), 
            escaphellarg($fecha_actual)
        );
        shell_exec($cmd);

        $mensaje_final = "SOLICITUD PROCESADA CON ÉXITO.";
        $es_error = false;
    } catch (Exception $e) {
        // AQUÍ ESTÁ LA MAGIA: Ahora veremos qué está fallando realmente
        $mensaje_final = "ERROR TÉCNICO: " . $e->getMessage();
        $es_error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ESTADO DE ENVÍO | LEXDEFENSOR</title>
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
        <h1><?php echo $es_error ? "SISTEMA_ERROR" : "SISTEMA_OK"; ?></h1>
        <p><?php echo $mensaje_final; ?></p>
    </header>

    <section class="contact-section" style="text-align: center;">
        <div class="contact-form">
            <p style="color: #000; margin-bottom: 2rem; font-family: 'Source Code Pro', monospace;">
                <?php if ($es_error): ?>
                    [!] <?php echo htmlspecialchars($mensaje_final); ?>
                <?php else: ?>
                    [+] Su reporte ha sido indexado correctamente. Un analista revisará el caso y contactará con usted en la dirección: <?php echo htmlspecialchars($email); ?>
                <?php endif; ?>
            </p>
            <a href="index.php" class="btn-primary" style="text-decoration:none; display:inline-block;">VOLVER AL PANEL</a>
        </div>
    </section>

    <footer class="footer-bottom">
        &copy; 2026 LEXDEFENSOR. LOG_TRANSACTION_COMPLETED.
    </footer>
</body>
</html>