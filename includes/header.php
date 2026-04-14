<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASD Morra De Sanctis 1982 🐺</title>
    
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    
    <?php if (isset($css_extra)): ?>
        <link rel="stylesheet" href="css/<?php echo $css_extra; ?>">
    <?php endif; ?>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                
                <li>
                    <a href="live.php" class="nav-link-live">
                        <span class="nav-live-dot"></span> LIVE
                    </a>
                </li>
                
                <?php if (isset($_SESSION['id_utente'])): ?>
                    <li><a href="card.php">📖 Almanacco</a></li>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['id_utente'])): ?>
                    <?php if (isset($_SESSION['ruolo']) && trim($_SESSION['ruolo']) === 'admin'): ?>
                        <li><a href="admin.php">🛠️ Gestione</a></li>
                    <?php endif; ?>
                    <li><a href="login.php?logout=1" class="logout">👤 Esci</a></li>
                <?php else: ?>
                    <li><a href="login.php">👤 Accedi</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<main class="content-area">