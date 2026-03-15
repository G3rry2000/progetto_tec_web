--
-- PostgreSQL database dump
--

\restrict Y0Uvfj68Le3htMMnioDdw27YpHZOOdPYFbKq6f47hySkkl9Mo8lRVPbpnBfDZS1

-- Dumped from database version 18.1
-- Dumped by pg_dump version 18.1

-- Started on 2026-03-15 16:27:26

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

ALTER TABLE ONLY public.messaggi DROP CONSTRAINT messaggi_id_utente_fkey;
ALTER TABLE ONLY public.utenti DROP CONSTRAINT utenti_pkey;
ALTER TABLE ONLY public.utenti DROP CONSTRAINT utenti_email_key;
ALTER TABLE ONLY public.messaggi DROP CONSTRAINT messaggi_pkey;
ALTER TABLE ONLY public.giocatori DROP CONSTRAINT giocatori_pkey;
ALTER TABLE public.utenti ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.messaggi ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.giocatori ALTER COLUMN id DROP DEFAULT;
DROP SEQUENCE public.utenti_id_seq;
DROP TABLE public.utenti;
DROP SEQUENCE public.messaggi_id_seq;
DROP TABLE public.messaggi;
DROP SEQUENCE public.giocatori_id_seq;
DROP TABLE public.giocatori;
SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 224 (class 1259 OID 16425)
-- Name: giocatori; Type: TABLE; Schema: public; Owner: www
--

CREATE TABLE public.giocatori (
    id integer NOT NULL,
    nome character varying(100) NOT NULL,
    nascita character varying(20),
    ruolo character varying(3),
    gol integer DEFAULT 0,
    presenze integer DEFAULT 0,
    ammonizioni integer DEFAULT 0,
    espulsioni integer DEFAULT 0,
    CONSTRAINT giocatori_ruolo_check CHECK (((ruolo)::text = ANY ((ARRAY['POR'::character varying, 'DIF'::character varying, 'CEN'::character varying, 'ATT'::character varying])::text[])))
);


ALTER TABLE public.giocatori OWNER TO www;

--
-- TOC entry 223 (class 1259 OID 16424)
-- Name: giocatori_id_seq; Type: SEQUENCE; Schema: public; Owner: www
--

CREATE SEQUENCE public.giocatori_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.giocatori_id_seq OWNER TO www;

--
-- TOC entry 5043 (class 0 OID 0)
-- Dependencies: 223
-- Name: giocatori_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: www
--

ALTER SEQUENCE public.giocatori_id_seq OWNED BY public.giocatori.id;


--
-- TOC entry 219 (class 1259 OID 16393)
-- Name: messaggi; Type: TABLE; Schema: public; Owner: www
--

CREATE TABLE public.messaggi (
    id integer NOT NULL,
    id_utente integer,
    testo text NOT NULL,
    data_invio timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.messaggi OWNER TO www;

--
-- TOC entry 220 (class 1259 OID 16401)
-- Name: messaggi_id_seq; Type: SEQUENCE; Schema: public; Owner: www
--

CREATE SEQUENCE public.messaggi_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.messaggi_id_seq OWNER TO www;

--
-- TOC entry 5044 (class 0 OID 0)
-- Dependencies: 220
-- Name: messaggi_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: www
--

ALTER SEQUENCE public.messaggi_id_seq OWNED BY public.messaggi.id;


--
-- TOC entry 221 (class 1259 OID 16402)
-- Name: utenti; Type: TABLE; Schema: public; Owner: www
--

CREATE TABLE public.utenti (
    id integer NOT NULL,
    nome character varying(50) NOT NULL,
    email character varying(100) NOT NULL,
    password character varying(255) NOT NULL,
    giocatore_preferito character varying(50),
    ruolo character varying(20) DEFAULT 'tifoso'::character varying
);


ALTER TABLE public.utenti OWNER TO www;

--
-- TOC entry 222 (class 1259 OID 16410)
-- Name: utenti_id_seq; Type: SEQUENCE; Schema: public; Owner: www
--

CREATE SEQUENCE public.utenti_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.utenti_id_seq OWNER TO www;

--
-- TOC entry 5045 (class 0 OID 0)
-- Dependencies: 222
-- Name: utenti_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: www
--

ALTER SEQUENCE public.utenti_id_seq OWNED BY public.utenti.id;


--
-- TOC entry 4870 (class 2604 OID 16428)
-- Name: giocatori id; Type: DEFAULT; Schema: public; Owner: www
--

ALTER TABLE ONLY public.giocatori ALTER COLUMN id SET DEFAULT nextval('public.giocatori_id_seq'::regclass);


--
-- TOC entry 4866 (class 2604 OID 16411)
-- Name: messaggi id; Type: DEFAULT; Schema: public; Owner: www
--

ALTER TABLE ONLY public.messaggi ALTER COLUMN id SET DEFAULT nextval('public.messaggi_id_seq'::regclass);


--
-- TOC entry 4868 (class 2604 OID 16412)
-- Name: utenti id; Type: DEFAULT; Schema: public; Owner: www
--

ALTER TABLE ONLY public.utenti ALTER COLUMN id SET DEFAULT nextval('public.utenti_id_seq'::regclass);


--
-- TOC entry 5037 (class 0 OID 16425)
-- Dependencies: 224
-- Data for Name: giocatori; Type: TABLE DATA; Schema: public; Owner: www
--

INSERT INTO public.giocatori VALUES (1, 'Caputo Davide', '09-06-2006', 'CEN', 3, 11, 1, 0);
INSERT INTO public.giocatori VALUES (2, 'Caputo Giancarmine', '15-04-2000', 'CEN', 0, 5, 0, 0);
INSERT INTO public.giocatori VALUES (3, 'Carino Gerardo', '29-06-2000', 'CEN', 3, 10, 2, 0);
INSERT INTO public.giocatori VALUES (4, 'Chieffo Alessandro', '28-09-1995', 'CEN', 0, 11, 3, 0);
INSERT INTO public.giocatori VALUES (5, 'Ciccone Gabriele', '27-01-2005', 'DIF', 0, 8, 1, 0);
INSERT INTO public.giocatori VALUES (6, 'Covino Benedetto', '24-10-2006', 'POR', 0, 11, 0, 0);
INSERT INTO public.giocatori VALUES (7, 'De Simone Bruno', '23-01-1998', 'ATT', 2, 9, 0, 0);
INSERT INTO public.giocatori VALUES (8, 'Di Leo Andrea', '14-05-2007', 'ATT', 0, 4, 0, 0);
INSERT INTO public.giocatori VALUES (9, 'Di Paola Rocco', '02-04-1985', 'ATT', 5, 11, 1, 0);
INSERT INTO public.giocatori VALUES (10, 'Di Paolo Pietro', '02-06-1998', 'CEN', 0, 10, 2, 0);


--
-- TOC entry 5032 (class 0 OID 16393)
-- Dependencies: 219
-- Data for Name: messaggi; Type: TABLE DATA; Schema: public; Owner: www
--

INSERT INTO public.messaggi VALUES (1, 1, 'Dai ragazzi portiamo a casa i 3 punti, non molliamo!!', '2026-02-24 13:04:33.000607');
INSERT INTO public.messaggi VALUES (2, 2, 'Mister, torniamo alle origini! Mettiamo giù un bel 4-4-2, compatti e cattivi in mezzo al campo. Meno esperimenti strani, più sostanza: è così che si portano a casa i 3 punti! Forza Lupi! 🐺🟢⚪', '2026-02-24 16:48:21.478685');
INSERT INTO public.messaggi VALUES (3, 3, 'Domenica voglio vedere gli spalti pieni! In Terza Categoria la differenza la fa chi ha più fame, e noi dobbiamo essere il dodicesimo uomo in campo. Trasformiamo il nostro campo in un fortino inespugnabile! Avanti Lupi! 🐺🟢⚪', '2026-02-24 16:54:08.922817');
INSERT INTO public.messaggi VALUES (4, 4, 'Ragazzi, per la trasferta di settimana prossima stiamo organizzando le macchine. Chi si unisce? Dobbiamo invadere il loro campetto e fargli vedere come tifa la gente di Morra De Sanctis! 🚗📢', '2026-02-25 15:10:23.473336');
INSERT INTO public.messaggi VALUES (5, 5, 'Ragazzi, vincere è bello, ma il terzo tempo al bar del paese tutti sporchi di fango è la vera magia della Terza Categoria. Portiamo a casa i 3 punti e poi offro io il primo giro! 🍻🐺', '2026-02-25 15:12:25.345928');
INSERT INTO public.messaggi VALUES (6, 6, 'Volevo fare un applauso ai ragazzini che domenica sono entrati dalla panchina con gli occhi della tigre. È questo l''attaccamento alla maglia che vogliamo vedere a Morra! Il futuro è nostro. 💪🟢⚪', '2026-02-25 15:14:02.117508');


--
-- TOC entry 5034 (class 0 OID 16402)
-- Dependencies: 221
-- Data for Name: utenti; Type: TABLE DATA; Schema: public; Owner: www
--

INSERT INTO public.utenti VALUES (1, 'Gabriele', 'cixgabry@gmail.com', '$2y$10$4n5.0tVS6phicVB6wsOBa.QCWddCLwBQ/I3KHweHKNdULUKR0QT3i', 'Peppo Show', 'tifoso');
INSERT INTO public.utenti VALUES (2, 'Gerardo', 'gerardocarino00@gmail.com', '$2y$10$K6bARJws40EW5bZAe.a1S.CqL/bLxgrwkeRyn81jjjhBVU/KFT59m', 'Donato Fruccio', 'tifoso');
INSERT INTO public.utenti VALUES (3, 'Donato', 'donatofiniello03@gmail.com', '$2y$10$VO8zoXqHl6Bal5V4McWUfu69h/s.xBHv/pxXONoL2UvrsCJMUY7Wu', 'Gabriele Ciccone', 'tifoso');
INSERT INTO public.utenti VALUES (4, 'Lucio', 'lupolucio07@gmail.com', '$2y$10$H32lFTTItdMQO5K.V23y3eyBzhukmSRTy8d6a5U3XJ3x.kdK.0DXS', 'Antonio Disapio', 'tifoso');
INSERT INTO public.utenti VALUES (5, 'Mirko', 'mirkodellapolla@gmail.com', '$2y$10$NRevZQTGXljwydVReV.c4eylCOyR0z9zr3Ex4p4l/BB4V11hg0D5O', 'Gerardo Carino', 'tifoso');
INSERT INTO public.utenti VALUES (6, 'Rosario', 'rosariobuscetto@gmail.com', '$2y$10$FrvRRs3s23wC27yCG2DuuOFm2h5DF0MtC3u3H/D/Je.9XDJSo5yvG', 'Cristian Lombardi ', 'tifoso');
INSERT INTO public.utenti VALUES (8, 'Mario Di Pietro', 'mariodipietro99@gmail.com', '$2y$10$02LLNH8QGLPC8rZa5E3WJOhh9XcgV6qenwYQwb9xiMxvxT87YmxcO', 'Davide Caputo', 'tifoso');
INSERT INTO public.utenti VALUES (7, 'Amministratore', 'admin@morra.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tutta la Squadra', 'admin');


--
-- TOC entry 5046 (class 0 OID 0)
-- Dependencies: 223
-- Name: giocatori_id_seq; Type: SEQUENCE SET; Schema: public; Owner: www
--

SELECT pg_catalog.setval('public.giocatori_id_seq', 10, true);


--
-- TOC entry 5047 (class 0 OID 0)
-- Dependencies: 220
-- Name: messaggi_id_seq; Type: SEQUENCE SET; Schema: public; Owner: www
--

SELECT pg_catalog.setval('public.messaggi_id_seq', 6, true);


--
-- TOC entry 5048 (class 0 OID 0)
-- Dependencies: 222
-- Name: utenti_id_seq; Type: SEQUENCE SET; Schema: public; Owner: www
--

SELECT pg_catalog.setval('public.utenti_id_seq', 8, true);


--
-- TOC entry 4883 (class 2606 OID 16437)
-- Name: giocatori giocatori_pkey; Type: CONSTRAINT; Schema: public; Owner: www
--

ALTER TABLE ONLY public.giocatori
    ADD CONSTRAINT giocatori_pkey PRIMARY KEY (id);


--
-- TOC entry 4877 (class 2606 OID 16414)
-- Name: messaggi messaggi_pkey; Type: CONSTRAINT; Schema: public; Owner: www
--

ALTER TABLE ONLY public.messaggi
    ADD CONSTRAINT messaggi_pkey PRIMARY KEY (id);


--
-- TOC entry 4879 (class 2606 OID 16416)
-- Name: utenti utenti_email_key; Type: CONSTRAINT; Schema: public; Owner: www
--

ALTER TABLE ONLY public.utenti
    ADD CONSTRAINT utenti_email_key UNIQUE (email);


--
-- TOC entry 4881 (class 2606 OID 16418)
-- Name: utenti utenti_pkey; Type: CONSTRAINT; Schema: public; Owner: www
--

ALTER TABLE ONLY public.utenti
    ADD CONSTRAINT utenti_pkey PRIMARY KEY (id);


--
-- TOC entry 4884 (class 2606 OID 16419)
-- Name: messaggi messaggi_id_utente_fkey; Type: FK CONSTRAINT; Schema: public; Owner: www
--

ALTER TABLE ONLY public.messaggi
    ADD CONSTRAINT messaggi_id_utente_fkey FOREIGN KEY (id_utente) REFERENCES public.utenti(id) ON DELETE CASCADE;


-- Completed on 2026-03-15 16:27:26

--
-- PostgreSQL database dump complete
--

\unrestrict Y0Uvfj68Le3htMMnioDdw27YpHZOOdPYFbKq6f47hySkkl9Mo8lRVPbpnBfDZS1

