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

// OGGETTO JSON-LIKE: Coordinate dei moduli in campo
// y = verticale (%), x = orizzontale (%), l = linea, o = ordine
const schemi = {
    "4-4-2": [
        {y:92, x:50, l:1, o:1}, {y:75, x:15, l:2, o:1}, {y:75, x:38, l:2, o:2}, {y:75, x:62, l:2, o:3}, {y:75, x:85, l:2, o:4},
        {y:45, x:15, l:3, o:1}, {y:45, x:38, l:3, o:2}, {y:45, x:62, l:3, o:3}, {y:45, x:85, l:3, o:4}, {y:15, x:35, l:5, o:1}, {y:15, x:65, l:5, o:2}
    ],
    "4-3-3": [
        {y:92, x:50, l:1, o:1}, {y:75, x:15, l:2, o:1}, {y:75, x:38, l:2, o:2}, {y:75, x:62, l:2, o:3}, {y:75, x:85, l:2, o:4},
        {y:45, x:25, l:3, o:1}, {y:45, x:50, l:3, o:2}, {y:45, x:75, l:3, o:3}, {y:15, x:20, l:5, o:1}, {y:15, x:50, l:5, o:2}, {y:15, x:80, l:5, o:3}
    ],
    "3-5-2": [
        {y:92, x:50, l:1, o:1}, {y:75, x:25, l:2, o:1}, {y:75, x:50, l:2, o:2}, {y:75, x:75, l:2, o:3},
        {y:45, x:10, l:3, o:1}, {y:45, x:30, l:3, o:2}, {y:45, x:50, l:3, o:3}, {y:45, x:70, l:3, o:4}, {y:45, x:90, l:3, o:5}, {y:15, x:35, l:5, o:1}, {y:15, x:65, l:5, o:2}
    ],
    "4-3-1-2": [
        {y:92, x:50, l:1, o:1}, // 1 Portiere
        {y:75, x:15, l:2, o:1}, {y:75, x:38, l:2, o:2}, {y:75, x:62, l:2, o:3}, {y:75, x:85, l:2, o:4}, // 4 Difensori
        {y:50, x:25, l:3, o:1}, {y:50, x:50, l:3, o:2}, {y:50, x:75, l:3, o:3}, // 3 Centrocampisti (leggermente arretrati)
        {y:32, x:50, l:4, o:1}, // 1 Trequartista (Linea 4 aggiunta per distanziarlo da centrocampo e attacco)
        {y:15, x:35, l:5, o:1}, {y:15, x:65, l:5, o:2} // 2 Attaccanti
    ]
};

function renderCampo() {
    const pitch = document.getElementById('pitch'); 
    pitch.innerHTML = '';
    
    // Legge il modulo da "moduloAttuale" dichiarato nel PHP
    const spots = schemi[moduloAttuale] || schemi["4-4-2"];
    
    spots.forEach(s => {
        const div = document.createElement('div'); 
        div.className = 'drop-spot';
        div.style.top = s.y + '%'; 
        div.style.left = s.x + '%'; 
        div.dataset.l = s.l; 
        div.dataset.o = s.o;
        div.ondragover = (e) => e.preventDefault(); 
        div.ondrop = onDrop; 
        pitch.appendChild(div);
    });
    loadSavedPlayers();
}

function loadSavedPlayers() {
    // Legge l'array "titolariInCampo" generato dal PHP
    titolariInCampo.forEach(g => {
        const spot = document.querySelector(`.drop-spot[data-l="${g.linea}"][data-o="${g.ordine}"]`);
        if (spot) {
            const cognome = g.nome.split(' ').pop().toUpperCase();
            spot.innerHTML = `
                <div class="player-token" draggable="true" ondragstart="onDragStart(event, 'field')" data-id="${g.id}">
                    <button class="remove-btn" onclick="removePlayer(${g.id})">×</button>
                    <span class="name">${cognome}</span><span class="role">${g.ruolo}</span>
                </div>`;
        }
    });
}

function onDragStart(ev, source) { 
    ev.dataTransfer.setData("id", ev.target.dataset.id); 
    ev.dataTransfer.setData("source", source); 
}

function onDrop(ev) {
    ev.preventDefault();
    const idTrascinato = ev.dataTransfer.getData("id"); 
    const source = ev.dataTransfer.getData("source");
    const spot = ev.currentTarget; 
    const targetToken = spot.querySelector('.player-token');
    
    if (targetToken) {
        const idPresente = targetToken.dataset.id;
        if(idTrascinato == idPresente) return;
        if (source === 'field') { 
            saveAjax('swap_players', { id_1: idTrascinato, id_2: idPresente, linea_target: spot.dataset.l, ordine_target: spot.dataset.o }).then(() => location.reload()); 
        }
    } else {
        saveAjax('save_pos', { id_g: idTrascinato, linea: spot.dataset.l, ordine: spot.dataset.o }).then(() => location.reload());
    }
}

function eseguiSostituzione() {
    const idEsce = document.getElementById('sub-esce').value; 
    const idEntra = document.getElementById('sub-entra').value;
    if (!idEsce || !idEntra) { alert("Seleziona i giocatori."); return; }
    if (confirm("Registrare la sostituzione nel database?")) { 
        saveAjax('make_sub', { id_esce: idEsce, id_entra: idEntra }).then(() => location.reload()); 
    }
}

function onDropBench(ev) { 
    ev.preventDefault(); 
    saveAjax('add_bench', { id_g: ev.dataTransfer.getData("id") }).then(() => location.reload()); 
}

function deleteSub(idSub) { 
    if(confirm("Eliminare il log?")) saveAjax('delete_sub', { id_sub: idSub }).then(() => location.reload()); 
}

function removePlayer(id) { 
    saveAjax('remove', { id_g: id }).then(() => location.reload()); 
}

function cambiaModulo(v) { 
    if(confirm("Reset formazione?")) saveAjax('change_modulo', { modulo: v }).then(() => location.reload()); 
}

function saveAjax(action, extra) {
    // Usa la variabile globale "idPartita" lasciata in admin.php
    const params = new URLSearchParams({ ajax_action: action, id: idPartita, ...extra });
    return fetch(window.location.href, { 
        method: 'POST', 
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}, 
        body: params 
    });
}

document.addEventListener('DOMContentLoaded', renderCampo);