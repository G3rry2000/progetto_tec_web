--
-- PostgreSQL database dump
--

\restrict u4ZFFXvbdcyGAlp20cE9cdES0YHJgv8NIokZISAYtFj8TrZ6Wt5ySvQIhY7I1jY

-- Dumped from database version 18.3
-- Dumped by pg_dump version 18.3

-- Started on 2026-03-20 17:52:39

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

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 226 (class 1259 OID 16612)
-- Name: classifica; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.classifica (
    id integer NOT NULL,
    squadra character varying(100) NOT NULL,
    pt integer,
    g integer,
    v integer,
    n integer,
    p integer,
    f integer,
    s integer,
    dr integer,
    is_morra boolean DEFAULT false
);


--
-- TOC entry 225 (class 1259 OID 16611)
-- Name: classifica_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.classifica_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 5066 (class 0 OID 0)
-- Dependencies: 225
-- Name: classifica_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.classifica_id_seq OWNED BY public.classifica.id;


--
-- TOC entry 220 (class 1259 OID 16567)
-- Name: giocatori; Type: TABLE; Schema: public; Owner: -
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
    CONSTRAINT giocatori_ruolo_check CHECK (((ruolo)::text = ANY (ARRAY[('POR'::character varying)::text, ('DIF'::character varying)::text, ('CEN'::character varying)::text, ('ATT'::character varying)::text])))
);


--
-- TOC entry 219 (class 1259 OID 16566)
-- Name: giocatori_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.giocatori_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 5068 (class 0 OID 0)
-- Dependencies: 219
-- Name: giocatori_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.giocatori_id_seq OWNED BY public.giocatori.id;


--
-- TOC entry 224 (class 1259 OID 16595)
-- Name: messaggi; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.messaggi (
    id integer NOT NULL,
    id_utente integer,
    testo text NOT NULL,
    data_invio timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- TOC entry 223 (class 1259 OID 16594)
-- Name: messaggi_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.messaggi_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 5069 (class 0 OID 0)
-- Dependencies: 223
-- Name: messaggi_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.messaggi_id_seq OWNED BY public.messaggi.id;


--
-- TOC entry 228 (class 1259 OID 16622)
-- Name: partite; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.partite (
    id integer NOT NULL,
    giornata integer,
    casa character varying(100),
    ospite character varying(100),
    gol_casa integer,
    gol_ospite integer,
    data_match character varying(20),
    giocata boolean DEFAULT false
);


--
-- TOC entry 227 (class 1259 OID 16621)
-- Name: partite_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.partite_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 5071 (class 0 OID 0)
-- Dependencies: 227
-- Name: partite_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.partite_id_seq OWNED BY public.partite.id;


--
-- TOC entry 222 (class 1259 OID 16581)
-- Name: utenti; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.utenti (
    id integer NOT NULL,
    nome character varying(50) NOT NULL,
    email character varying(100) NOT NULL,
    password character varying(255) NOT NULL,
    giocatore_preferito character varying(50),
    ruolo character varying(20) DEFAULT 'tifoso'::character varying
);


--
-- TOC entry 221 (class 1259 OID 16580)
-- Name: utenti_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.utenti_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 5072 (class 0 OID 0)
-- Dependencies: 221
-- Name: utenti_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.utenti_id_seq OWNED BY public.utenti.id;


--
-- TOC entry 4885 (class 2604 OID 16615)
-- Name: classifica id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.classifica ALTER COLUMN id SET DEFAULT nextval('public.classifica_id_seq'::regclass);


--
-- TOC entry 4876 (class 2604 OID 16570)
-- Name: giocatori id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.giocatori ALTER COLUMN id SET DEFAULT nextval('public.giocatori_id_seq'::regclass);


--
-- TOC entry 4883 (class 2604 OID 16598)
-- Name: messaggi id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.messaggi ALTER COLUMN id SET DEFAULT nextval('public.messaggi_id_seq'::regclass);


--
-- TOC entry 4887 (class 2604 OID 16625)
-- Name: partite id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.partite ALTER COLUMN id SET DEFAULT nextval('public.partite_id_seq'::regclass);


--
-- TOC entry 4881 (class 2604 OID 16584)
-- Name: utenti id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.utenti ALTER COLUMN id SET DEFAULT nextval('public.utenti_id_seq'::regclass);


--
-- TOC entry 5057 (class 0 OID 16612)
-- Dependencies: 226
-- Data for Name: classifica; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.classifica (id, squadra, pt, g, v, n, p, f, s, dr, is_morra) FROM stdin;
1	Castelfranci	32	12	10	2	0	30	2	28	f
2	Teora	30	13	9	3	1	42	8	34	f
3	FC Montemarano	29	13	9	2	2	37	17	20	f
4	Nusco '75	25	12	8	1	3	32	16	16	f
5	Andretta	22	13	6	4	3	23	17	6	f
6	Morra De Sanctis	20	13	6	2	5	23	25	-2	t
7	Montella Football Academy	13	13	3	4	6	24	24	0	f
8	S.S. Giuseppe Siconolfi	9	12	2	3	7	16	27	-11	f
9	Sporting Paternopoli	8	13	2	2	9	13	35	-22	f
10	Frigento Sturno Sq. B	4	13	0	4	9	14	54	-40	f
11	Villamaina	3	13	0	3	10	15	44	-29	f
\.


--
-- TOC entry 5051 (class 0 OID 16567)
-- Dependencies: 220
-- Data for Name: giocatori; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.giocatori (id, nome, nascita, ruolo, gol, presenze, ammonizioni, espulsioni) FROM stdin;
1	Caputo Davide	09-06-2006	CEN	4	2	0	0
2	Caputo Giancarmine	15-04-2000	CEN	0	0	0	0
3	Carino Gerardo	29-06-2000	CEN	3	2	0	0
4	Chieffo Alessandro	28-09-1995	CEN	0	2	0	0
5	Ciccone Gabriele	27-01-2005	DIF	0	0	0	0
6	Covino Benedetto	24-10-2006	POR	0	0	0	0
7	De Simone Bruno	23-01-1998	ATT	2	0	0	0
8	Di Leo Andrea	14-05-2007	ATT	0	2	0	0
9	Di Paola Rocco	02-04-1985	ATT	0	0	0	0
10	Di Paolo Pietro	02-06-1998	CEN	1	0	0	0
11	Di Pietro Giuseppe	14-03-2006	ATT	3	2	0	0
12	Di Pietro Mario	26-01-1999	DIF	0	2	0	0
13	Di Sapio Antonio	25-09-1996	ATT	2	2	0	0
14	Famiglietti Michele	31-10-1993	CEN	1	0	0	0
15	Fruccio Donato	28-04-2008	DIF	0	2	0	0
16	Iannone Francesco	19-04-1988	CEN	3	0	0	0
17	Lombardi Cristian	30-12-2005	CEN	0	1	0	0
18	Mignone Gerardo Valentino	14-02-2005	ATT	1	0	0	0
19	Pelosi Renzo	04-12-1996	DIF	0	0	0	0
20	Peluso Dennis Antonio	27-06-2005	POR	1	1	0	0
21	Pennella Antony	18-01-2002	DIF	1	1	0	0
22	Rullo Carmine	20-09-1999	DIF	0	2	0	0
23	Sperduto Marco	20-06-1997	DIF	0	0	0	0
24	Vitiello Roberto	12-11-1998	POR	0	1	0	0
\.


--
-- TOC entry 5055 (class 0 OID 16595)
-- Dependencies: 224
-- Data for Name: messaggi; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.messaggi (id, id_utente, testo, data_invio) FROM stdin;
1	1	Dai ragazzi portiamo a casa i 3 punti, non molliamo!!	2026-02-24 13:04:33.000607
2	2	Mister, torniamo alle origini! Mettiamo giù un bel 4-4-2, compatti e cattivi...	2026-02-24 16:48:21.478685
3	3	Domenica voglio vedere gli spalti pieni!	2026-02-24 16:54:08.922817
4	4	Ragazzi, per la trasferta di settimana prossima stiamo organizzando le macchine.	2026-02-25 15:10:23.473336
5	5	Portiamo a casa i 3 punti e poi offro io il primo giro! 🍻	2026-02-25 15:12:25.345928
6	6	Volevo fare un applauso ai ragazzini che domenica sono entrati dalla panchina.	2026-02-25 15:14:02.117508
\.


--
-- TOC entry 5059 (class 0 OID 16622)
-- Dependencies: 228
-- Data for Name: partite; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.partite (id, giornata, casa, ospite, gol_casa, gol_ospite, data_match, giocata) FROM stdin;
1	1	Montella Academy	Morra De Sanctis	2	2	\N	t
2	2	Morra De Sanctis	Teora	0	1	\N	t
3	3	Villamaina	Morra De Sanctis	1	3	\N	t
4	4	Morra De Sanctis	Sporting Paternopoli	1	1	\N	t
5	5	S.S. Giuseppe Siconolfi	Morra De Sanctis	1	3	\N	t
6	6	Morra De Sanctis	Castelfranci	0	4	\N	t
7	7	Frigento Sturno Sq. B	Morra De Sanctis	1	6	\N	t
8	8	Morra De Sanctis	Andretta	1	0	\N	t
9	10	FC Montemarano	Morra De Sanctis	3	0	\N	t
10	11	Morra De Sanctis	Nusco '75	1	4	\N	t
11	12	Morra De Sanctis	Montella Academy	3	1	\N	t
12	13	Teora	Morra De Sanctis	5	0	\N	t
13	14	Morra De Sanctis	Villamaina	3	1	\N	t
14	15	Sporting Paternopoli	Morra De Sanctis	\N	\N	22/03	f
15	16	Morra De Sanctis	S.S. Giuseppe Siconolfi	\N	\N	29/03	f
16	17	Castelfranci	Morra De Sanctis	\N	\N	12/04	f
17	18	Morra De Sanctis	Frigento Sturno Sq. B	\N	\N	19/04	f
18	19	Andretta	Morra De Sanctis	\N	\N	26/04	f
19	21	Morra De Sanctis	FC Montemarano	\N	\N	10/05	f
20	22	Nusco '75	Morra De Sanctis	\N	\N	17/05	f
\.


--
-- TOC entry 5053 (class 0 OID 16581)
-- Dependencies: 222
-- Data for Name: utenti; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.utenti (id, nome, email, password, giocatore_preferito, ruolo) FROM stdin;
1	Gabriele	cixgabry@gmail.com	$2y$10$4n5.0tVS6phicVB6wsOBa.QCWddCLwBQ/I3KHweHKNdULUKR0QT3i	Peppo Show	tifoso
2	Gerardo	gerardocarino00@gmail.com	$2y$10$K6bARJws40EW5bZAe.a1S.CqL/bLxgrwkeRyn81jjjhBVU/KFT59m	Donato Fruccio	tifoso
3	Donato	donatofiniello03@gmail.com	$2y$10$VO8zoXqHl6Bal5V4McWUfu69h/s.xBHv/pxXONoL2UvrsCJMUY7Wu	Gabriele Ciccone	tifoso
4	Lucio	lupolucio07@gmail.com	$2y$10$H32lFTTItdMQO5K.V23y3eyBzhukmSRTy8d6a5U3XJ3x.kdK.0DXS	Antonio Disapio	tifoso
5	Mirko	mirkodellapolla@gmail.com	$2y$10$NRevZQTGXljwydVReV.c4eylCOyR0z9zr3Ex4p4l/BB4V11hg0D5O	Gerardo Carino	tifoso
6	Rosario	rosariobuscetto@gmail.com	$2y$10$FrvRRs3s23wC27yCG2DuuOFm2h5DF0MtC3u3H/D/Je.9XDJSo5yvG	Cristian Lombardi 	tifoso
8	Mario Di Pietro	mariodipietro99@gmail.com	$2y$10$02LLNH8QGLPC8rZa5E3WJOhh9XcgV6qenwYQwb9xiMxvxT87YmxcO	Davide Caputo	tifoso
7	Amministratore	admin@morra.it	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	Tutta la Squadra	admin
\.


--
-- TOC entry 5073 (class 0 OID 0)
-- Dependencies: 225
-- Name: classifica_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.classifica_id_seq', 11, true);


--
-- TOC entry 5074 (class 0 OID 0)
-- Dependencies: 219
-- Name: giocatori_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.giocatori_id_seq', 24, true);


--
-- TOC entry 5075 (class 0 OID 0)
-- Dependencies: 223
-- Name: messaggi_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.messaggi_id_seq', 6, true);


--
-- TOC entry 5076 (class 0 OID 0)
-- Dependencies: 227
-- Name: partite_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.partite_id_seq', 20, true);


--
-- TOC entry 5077 (class 0 OID 0)
-- Dependencies: 221
-- Name: utenti_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.utenti_id_seq', 8, true);


--
-- TOC entry 4899 (class 2606 OID 16620)
-- Name: classifica classifica_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.classifica
    ADD CONSTRAINT classifica_pkey PRIMARY KEY (id);


--
-- TOC entry 4891 (class 2606 OID 16579)
-- Name: giocatori giocatori_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.giocatori
    ADD CONSTRAINT giocatori_pkey PRIMARY KEY (id);


--
-- TOC entry 4897 (class 2606 OID 16605)
-- Name: messaggi messaggi_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.messaggi
    ADD CONSTRAINT messaggi_pkey PRIMARY KEY (id);


--
-- TOC entry 4901 (class 2606 OID 16629)
-- Name: partite partite_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.partite
    ADD CONSTRAINT partite_pkey PRIMARY KEY (id);


--
-- TOC entry 4893 (class 2606 OID 16593)
-- Name: utenti utenti_email_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.utenti
    ADD CONSTRAINT utenti_email_key UNIQUE (email);


--
-- TOC entry 4895 (class 2606 OID 16591)
-- Name: utenti utenti_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.utenti
    ADD CONSTRAINT utenti_pkey PRIMARY KEY (id);


--
-- TOC entry 4902 (class 2606 OID 16606)
-- Name: messaggi messaggi_id_utente_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.messaggi
    ADD CONSTRAINT messaggi_id_utente_fkey FOREIGN KEY (id_utente) REFERENCES public.utenti(id) ON DELETE CASCADE;


--
-- TOC entry 5065 (class 0 OID 0)
-- Dependencies: 226
-- Name: TABLE classifica; Type: ACL; Schema: public; Owner: -
--

GRANT ALL ON TABLE public.classifica TO www;


--
-- TOC entry 5067 (class 0 OID 0)
-- Dependencies: 225
-- Name: SEQUENCE classifica_id_seq; Type: ACL; Schema: public; Owner: -
--

GRANT ALL ON SEQUENCE public.classifica_id_seq TO www;


--
-- TOC entry 5070 (class 0 OID 0)
-- Dependencies: 228
-- Name: TABLE partite; Type: ACL; Schema: public; Owner: -
--

GRANT ALL ON TABLE public.partite TO www;


-- Completed on 2026-03-20 17:52:39

--
-- PostgreSQL database dump complete
--

\unrestrict u4ZFFXvbdcyGAlp20cE9cdES0YHJgv8NIokZISAYtFj8TrZ6Wt5ySvQIhY7I1jY

