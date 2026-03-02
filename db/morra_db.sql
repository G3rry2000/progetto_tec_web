-- 1. CREAZIONE UTENTE WWW (Se non esiste) E PERMESSI
-- Nota: Se l'utente esiste già, la prima riga darà un avviso, ignoralo.
DO $$ 
BEGIN
  IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'www') THEN
    CREATE ROLE www WITH LOGIN PASSWORD 'www';
  END IF;
END $$;

ALTER ROLE www WITH SUPERUSER;

-- 2. PULIZIA (Opzionale: cancella le tabelle se vuoi ricominciare da zero pulito)
DROP TABLE IF EXISTS public.messaggi CASCADE;
DROP TABLE IF EXISTS public.utenti CASCADE;

-- 3. CREAZIONE TABELLA UTENTI
CREATE TABLE public.utenti (
    id SERIAL PRIMARY KEY,
    nome character varying(50) NOT NULL,
    email character varying(100) UNIQUE NOT NULL,
    password character varying(255) NOT NULL,
    giocatore_preferito character varying(50),
    ruolo character varying(20) DEFAULT 'tifoso'
);

-- 4. CREAZIONE TABELLA MESSAGGI
CREATE TABLE public.messaggi (
    id SERIAL PRIMARY KEY,
    id_utente integer REFERENCES public.utenti(id) ON DELETE CASCADE,
    testo text NOT NULL,
    data_invio timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);

-- 5. INSERIMENTO UTENTI DI TEST
INSERT INTO public.utenti (id, nome, email, password, giocatore_preferito, ruolo) VALUES 
(1, 'Gabriele', 'cixgabry@gmail.com', '$2y$10$4n5.0tVS6phicVB6wsOBa.QCWddCLwBQ/I3KHweHKNdULUKR0QT3i', 'Peppo Show', 'tifoso'),
(2, 'Gerardo', 'gerardocarino00@gmail.com', '$2y$10$K6bARJws40EW5bZAe.a1S.CqL/bLxgrwkeRyn81jjjhBVU/KFT59m', 'Donato Fruccio', 'tifoso'),
(3, 'Donato', 'donatofiniello03@gmail.com', '$2y$10$VO8zoXqHl6Bal5V4McWUfu69h/s.xBHv/pxXONoL2UvrsCJMUY7Wu', 'Gabriele Ciccone', 'tifoso'),
(4, 'Lucio', 'lupolucio07@gmail.com', '$2y$10$H32lFTTItdMQO5K.V23y3eyBzhukmSRTy8d6a5U3XJ3x.kdK.0DXS', 'Antonio Disapio', 'tifoso'),
(5, 'Mirko', 'mirkodellapolla@gmail.com', '$2y$10$NRevZQTGXljwydVReV.c4eylCOyR0z9zr3Ex4p4l/BB4V11hg0D5O', 'Gerardo Carino', 'tifoso'),
(6, 'Rosario', 'rosariobuscetto@gmail.com', '$2y$10$FrvRRs3s23wC27yCG2DuuOFm2h5DF0MtC3u3H/D/Je.9XDJSo5yvG', 'Cristian Lombardi ', 'tifoso');

-- 6. INSERIMENTO MESSAGGI DI TEST (I messaggi della Curva!)
INSERT INTO public.messaggi (id_utente, testo) VALUES 
(1, 'Dai ragazzi portiamo a casa i 3 punti, non molliamo!!'),
(2, 'Mister, torniamo alle origini! Mettiamo giù un bel 4-4-2, compatti e cattivi! 🐺'),
(3, 'Domenica voglio vedere gli spalti pieni! Avanti Lupi! 🟢⚪'),
(5, 'Portiamo a casa i 3 punti e poi offro io il primo giro al bar! 🍻');

-- 7. ASSEGNAZIONE FINALE PERMESSI A WWW
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO www;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO www;