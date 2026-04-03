<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASD Morra De Sanctis 🐺</title>
    
    <link rel="stylesheet" href="css/style.css">
    <?php if (isset($css_extra)): ?>
        <link rel="stylesheet" href="css/<?php echo $css_extra; ?>">
    <?php endif; ?>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* Allinea il container principale orizzontalmente */
        .header-container {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: space-between !important;
            width: 100%;
        }

        /* Logo e testo sulla stessa riga */
        .logo a {
            display: inline-flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 15px !important;
            white-space: nowrap !important;
            transition: transform 0.3s ease;
        }
        .logo a:hover { transform: scale(1.02); }

        /* Forza la lista dei link in orizzontale */
        .main-nav ul {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 10px !important;
            margin: 0 !important;
            padding: 0 !important;
            list-style: none !important;
        }

        /* Stile base dei link: in riga, con transizione fluida */
        .main-nav a {
            display: inline-flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 6px !important;
            white-space: nowrap !important; 
            padding: 8px 12px !important;
            border-radius: 8px !important;
            transition: all 0.3s ease !important;
            text-decoration: none;
        }

        /* EFFETTO HOVER */
        .main-nav a:hover {
            background-color: rgba(255, 255, 255, 0.2) !important;
            transform: translateY(-2px) !important;
        }

        /* --- ANIMAZIONE PALLINO ROSSO LIVE --- */
        @keyframes pulse-red-nav {
            0% { box-shadow: 0 0 0 0 rgba(255, 68, 68, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(255, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 68, 68, 0); }
        }
        
        .nav-live-dot {
            display: inline-block !important;
            width: 10px !important;
            height: 10px !important;
            background-color: #ff4444 !important;
            border-radius: 50% !important;
            animation: pulse-red-nav 1.5s infinite !important;
            flex-shrink: 0 !important; /* Impedisce al pallino di deformarsi */
        }

        .nav-link-live {
            color: #ffbaba !important; 
            font-weight: 900 !important;
        }
        .nav-link-live:hover {
            background-color: rgba(255, 68, 68, 0.2) !important; 
            color: #ffffff !important;
        }
    </style>
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