// Gestione dei Tab in login.php
function cambiaTab(tabId) {
    // Nascondi tutti i tab
    document.getElementById('tab-login').style.display = 'none';
    document.getElementById('tab-registrati').style.display = 'none';
    
    // Rimuovi classe active dai bottoni
    document.getElementById('btnLogin').classList.remove('active');
    document.getElementById('btnRegistrati').classList.remove('active');
    
    // Mostra il tab richiesto
    if (tabId === 'login') {
        document.getElementById('tab-login').style.display = 'block';
        document.getElementById('btnLogin').classList.add('active');
    } else {
        document.getElementById('tab-registrati').style.display = 'block';
        document.getElementById('btnRegistrati').classList.add('active');
    }
}

// Validazione Login lato client
function validaLogin() {
    const email = document.getElementById('login-email').value;
    const erroreLabel = document.getElementById('errore-js-login');
    
    if (!email.includes('@') || !email.includes('.')) {
        erroreLabel.textContent = "Inserisci un indirizzo email valido.";
        return false; 
    }
    
    erroreLabel.textContent = "";
    return true; 
}

// Validazione Registrazione lato client
function validaRegistrazione() {
    const email = document.getElementById('reg-email').value;
    const password = document.getElementById('reg-password').value;
    const erroreLabel = document.getElementById('errore-js-reg');
    
    if (!email.includes('@') || !email.includes('.')) {
        erroreLabel.textContent = "Inserisci un indirizzo email valido.";
        return false;
    }
    
    if (password.length < 6) {
        erroreLabel.textContent = "La password deve contenere almeno 6 caratteri.";
        return false;
    }
    
    erroreLabel.textContent = "";
    return true;
}