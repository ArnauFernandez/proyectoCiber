<?php
session_start();
// Comprobamos si el usuario está logueado para personalizar la vista
$logueado = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto | LexDefensor</title>
    <link rel="stylesheet" href="/css/style.css">
    <!-- Mantenemos la fuente de consola para coherencia con tu CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="navbar">
            <a href="/" style="text-decoration: none;">
                <div class="logo">LEX<span>DEFENSOR</span></div>
            </a>
            <ul class="nav-links">
                <li><a href="/">Nosotros</a></li>
                <li><a href="/ciberdelitos/">Ciberdelitos</a></li>
                <li><a href="/contacto/">Contacto</a></li>
                
                <?php if (!$logueado): ?>
                    <li><a href="/contacto/login.php" class="btn-primary" style="padding: 5px 15px; margin-left: 1rem;">Iniciar Sesión</a></li>
                <?php else: ?>
                    <li><a href="/contacto/logout.php" style="color: #d4af37; margin-left: 2rem; font-size: 0.9rem;">CERRAR SESIÓN (<?php echo strtoupper($_SESSION['user_nombre']); ?>)</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <section class="page-header">
        <h1>Contacte con un especialista</h1>
        <?php if ($logueado): ?>
            <p>SESIÓN ACTIVA: Acceso autorizado para <?php echo $_SESSION['user_email']; ?></p>
        <?php endif; ?>
    </section>

    <section class="contact-section">
        <?php if ($logueado): ?>
            <!-- FORMULARIO OPTIMIZADO: Email invisible, se procesa en send.php vía $_SESSION -->
            <form class="contact-form" action="/contacto/send.php" method="POST">
                
                <label style="color: #000; font-weight: bold; font-size: 0.8rem;">IDENTIDAD DEL SOLICITANTE</label>
                <input type="text" name="nombre" value="<?php echo $_SESSION['user_nombre']; ?>" readonly style="background: #f0f0f0; cursor: not-allowed; opacity: 0.7;">
                
                <!-- El campo email no se envía por el formulario, se recupera en send.php de la sesión -->

                <label style="color: #000; font-weight: bold; font-size: 0.8rem;">NATURALEZA DEL INCIDENTE</label>
                <select name="delito" required>
			<option value="Estafa">Estafa Bancaria / Phishing</option>
    			<option value="Identidad">Suplantación de Identidad</option>
    			<option value="Ataque">Ataque a Empresa</option>
    			<option value="Otro">Otro caso</option>
                </select>
                
                <label style="color: #000; font-weight: bold; font-size: 0.8rem;">DETALLES DEL CASO</label>
                <textarea name="mensaje" placeholder="Describa los hechos de forma breve para una primera valoración legal..." required></textarea>
                
                <button type="submit" class="btn-primary" style="border:none; cursor:pointer;">ENVIAR SOLICITUD DE ASISTENCIA</button>
            </form>

        <?php else: ?>
            <!-- AVISO DE ACCESO RESTRINGIDO -->
            <div class="contact-form" style="text-align: center;">
                <h3 style="color: #c0392b; font-family: 'Source Code Pro', monospace;">[ ACCESS_DENIED ]</h3>
                <p style="color: #333; margin: 1.5rem 0;">Por motivos de seguridad y cumplimiento de la LOPD, solo los usuarios autenticados pueden realizar consultas legales.</p>
                <a href="/contacto/login.php" class="btn-primary" style="text-decoration:none; display:inline-block;">IDENTIFICARSE EN EL SISTEMA</a>
            </div>
        <?php endif; ?>
    </section>

    <footer class="footer-bottom">
        <p>&copy; 2026 LexDefensor. Especialistas en Derecho Digital. // CONSOLE_MODE_ACTIVE</p>
    </footer>
</body>
</html>
