<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASD Morra De Sanctis 🐺</title>
    
    <link rel="stylesheet" href="css/style.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
</head>
<body>

<header class="main-header">
    <div class="header-container">
        <div class="logo">
            <a href="index.php">
                <img src="images/logo.png" alt="Logo ASD Morra" class="logo-img">
                <span>ASD MORRA DE SANCTIS</span>
            </a>
        </div>
        
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">🏠 Home</a></li>
                <li><a href="stagione.php">🏆 La Stagione</a></li>
                <li><a href="curva.php">📣 La Curva</a></li>
                
                <?php if (isset($_SESSION['id_utente'])): ?>
                    <?php if (isset($_SESSION['ruolo']) && trim($_SESSION['ruolo']) === 'admin'): ?>
                        <li><a href="admin.php" class="btn-admin">🛠️ Gestione</a></li>
                    <?php endif; ?>
                    <li><a href="login.php?logout=1" class="btn-login logout">👤 Esci</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn-login">👤 Accedi</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<main class="content-area">