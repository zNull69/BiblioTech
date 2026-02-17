CREATE DATABASE IF NOT EXISTS bibliotech_db;
USE bibliotech_db;

CREATE TABLE Utente (
    IdUtente      INT AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(100)  NOT NULL,
    cognome       VARCHAR(100)  NOT NULL,
    email         VARCHAR(255)  UNIQUE NOT NULL,
    password      VARCHAR(255)  NOT NULL,
    authCode      VARCHAR(255)  NOT NULL,
    codiceFiscale VARCHAR(16)   NOT NULL,
    ruolo         ENUM('studente', 'admin') NOT NULL
);

CREATE TABLE Libro (
    IdLibro    INT AUTO_INCREMENT PRIMARY KEY,
    titolo     VARCHAR(255) NOT NULL,
    autore     VARCHAR(255) NOT NULL,
    genere     VARCHAR(100) NOT NULL,
    etaTarget  INT          NOT NULL
);

CREATE TABLE Copia (
    IdCopia INT AUTO_INCREMENT PRIMARY KEY,
    IdLibro INT NOT NULL,
    stato   ENUM('disponibile', 'prestito') NOT NULL DEFAULT 'disponibile',
    FOREIGN KEY (IdLibro) REFERENCES Libro(IdLibro) ON DELETE CASCADE
);

CREATE TABLE Prestito (
    IdPrestito       INT AUTO_INCREMENT PRIMARY KEY,
    IdCopia          INT  NOT NULL,
    IdUtente         INT  NOT NULL,
    dataPrestito     DATE NOT NULL,
    dataScadenza     DATE NOT NULL,
    dataRestituzione DATE DEFAULT NULL,
    FOREIGN KEY (IdCopia)  REFERENCES Copia(IdCopia)   ON DELETE CASCADE,
    FOREIGN KEY (IdUtente) REFERENCES Utente(IdUtente) ON DELETE CASCADE
);

CREATE TABLE Sessione (
    IdSessione        INT AUTO_INCREMENT PRIMARY KEY,
    IdUtente          INT          NOT NULL,
    tokenSessione     VARCHAR(255) UNIQUE NOT NULL,
    lastLogin         DATETIME     NOT NULL,
    scadenzaSessione  DATETIME     NOT NULL,
    FOREIGN KEY (IdUtente) REFERENCES Utente(IdUtente) ON DELETE CASCADE
);

-- ---------------------------------------------------------------------
-- utenti di test
-- password: password123
-- authCode: 215700
-- ---------------------------------------------------------------------
INSERT INTO Utente (nome, cognome, email, password, authCode, codiceFiscale, ruolo) VALUES
(
    'Mario', 'Rossi',
    'mario.rossi@panettipitagora.edu.it',
    '$2b$12$JV.blEFLXP40qLCOCy8ELOsgIYyAoRb7aEQ.97Ca5ysghBCBlptK6',
    '$2b$12$AJcxiapfGYDJ9X3wp5JRH.jaHXJpMCDKWFcOsR7SJ4ak1PceAi/fK',
    'RSSMRA95A01H501Z',
    'studente'
),
(
    'Laura', 'Bianchi',
    'laura.bianchi@panettipitagora.edu.it',
    '$2b$12$pEv5xoInhnPFUHqNU4w.ReVupECU9NbLJ5FIDhb37tuKDySB1sL3a',
    '$2b$12$UcmGyuzd5DkUuitP0NwjGeurrnk1SmbFtU/XAJUsxHAgYSWSQ4OiO',
    'BNCLRA98M41H501W',
    'studente'
),
(
    'Anna', 'Neri',
    'anna.neri@panettipitagora.edu.it',
    '$2b$12$aAZhd0fQsl3lSCYK.JJQ.ufozYlunBWyZZbDaBiWGfCRyeW..afAi',
    '$2b$12$kiMi99vEwUxT4.pPIDO6sOWTdI3Iltm7D6OJvyNV4u1vHS9FIFDhS',
    'NRENNA85T41H501X',
    'studente'
),
(
    'Francesco', 'Tenerelli',
    'tenerelli.francesco@panettipitagora.edu.it',
    '$2b$12$aAZhd0fQsl3lSCYK.JJQ.ufozYlunBWyZZbDaBiWGfCRyeW..afAi',
    '$2b$12$kiMi99vEwUxT4.pPIDO6sOWTdI3Iltm7D6OJvyNV4u1vHS9FIFDhS',
    'TNRFNC08B22A662A',
    'admin'
);

-- ---------------------------------------------------------------------
-- Libri di test
-- ---------------------------------------------------------------------
INSERT INTO Libro (titolo, autore, genere, etaTarget) VALUES
('Il Piccolo Principe',                 'Antoine de Saint-Exupéry', 'Favola',    8),
('Harry Potter e la Pietra Filosofale', 'J.K. Rowling',             'Fantasy',   10),
('Il Signore degli Anelli',             'J.R.R. Tolkien',           'Fantasy',   12),
('Orgoglio e Pregiudizio',              'Jane Austen',              'Romantico', 14),
('1984',                                'George Orwell',            'Distopico', 16);

-- ---------------------------------------------------------------------
-- Copie di test
-- ---------------------------------------------------------------------
INSERT INTO Copia (IdLibro, stato) VALUES
(1, 'disponibile'), (1, 'disponibile'), (1, 'disponibile'), (1, 'disponibile'),
(2, 'disponibile'), (2, 'disponibile'), (2, 'disponibile'),
(3, 'disponibile'), (3, 'disponibile'), (3, 'disponibile'),
(4, 'disponibile'), (4, 'disponibile'),
(5, 'disponibile'), (5, 'disponibile');