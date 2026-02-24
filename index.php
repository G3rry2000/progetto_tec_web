<?php 
// 1. Richiamiamo la "testa" del sito (Menu, Logo, Colori)
include 'includes/header.php'; 
?>

<div class="home-container" style="text-align: center; padding: 40px 0;">
    
    <h1 style="font-size: 40px; margin-bottom: 10px;">Benvenuti nella Tana del Lupo! 🐺</h1>
    <h2 style="color: var(--verde-squadra); margin-bottom: 30px;">La casa ufficiale dell'ASD Morra De Sanctis</h2>
    
    <p style="font-size: 18px; line-height: 1.6; max-width: 800px; margin: 0 auto 40px auto;">
        Dimenticate i campi in erba perfetta e i riflettori da stadio. Qui si respira l'essenza vera del calcio: 
        <strong>sudore, terra battuta e orgoglio biancoverde.</strong><br>
        Segui la nostra marcia nel campionato di Terza Categoria campana, supporta i ragazzi e unisciti al branco!
    </p>

    <div class="pulsanti-home" style="display: flex; gap: 20px; justify-content: center; margin-top: 30px;">
        <a href="stagione.php" style="background-color: var(--verde-squadra); color: white; padding: 15px 30px; text-decoration: none; font-size: 18px; font-weight: bold; border-radius: 5px; text-transform: uppercase;">
            🏆 Guarda il Calendario
        </a>
        <a href="curva.php" style="background-color: #333; color: white; padding: 15px 30px; text-decoration: none; font-size: 18px; font-weight: bold; border-radius: 5px; text-transform: uppercase;">
            📣 Entra in Curva
        </a>
    </div>

</div>
<?php 
// 2. Richiamiamo la "coda" del sito (Motto, Copyright)
include 'includes/footer.php'; 
?>