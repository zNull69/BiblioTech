# BiblioTech

Piattaforma web nata dal desiderio di migrare dall’obsoleto registro cartaceo utilizzato per la gestione e amministrazione dei prestiti librai, ad una soluzione digitale e innovativa, Denominata “BiblioTech” il cui scopo sarà quello di amministrare le pratiche di prestito dei libri a disposizione, mantenendo per tutta la durata del ciclo di vita del prestito, il monopolio logistico e funzionale del sistema.

**ANALISI E SPECIFICHE DOCUMENTATE E ACCESSIBILI IN "./docs"**
---
## Funzionalità Principali

- Autenticazione con tre fattori: email, password e authCode univoco
- Separazione degli accessi per ruolo, verificata a ogni richiesta tramite query al database
- Catalogo filtrato per età, calcolata dal Codice Fiscale dell'utente
- Limite di 3 prestiti contemporanei per studente, con durata di 30 giorni
- Restituzione libri con aggiornamento automatico della disponibilità

---

## Stack Tecnologico

- Back-end: PHP 8 + Apache
- Front-end: HTML5 - CSS3 + BOOTSTRAP
- Database: MySQL
- Admin DB: phpMyAdmin
- Ambiente: Docker

---

## Avvio della web-app

Posizionarsi nella directory del progetto ed eseguire dal terminale di Docker:

```bash
cd /directory/del/progetto
docker compose build
docker compose up
```

I container avviati saranno:
- **php-apache** → `http://localhost:8080`
- **phpMyAdmin** → `http://localhost:8081`
- **MySQL** 

---

## Inizializzazione del Database

Prima di accedere alla piattaforma è necessario eseguire il dump del database:

1. Aprire **phpMyAdmin** su `http://localhost:8081`
3. Selezionare il database `bibliotech`
4. Aprire la scheda **SQL**
5. Incollare ed eseguire il contenuto del file `database.sql` accessibile nella directory ./sql del progetto

!!! Senza le tabelle con i dati di test, si potrà esclusivamente esplorare la piattaforma come studente, senza possibilità di accedere alle funzionalità dell'admin !!!

---

## Accesso alla Piattaforma

Una volta eseguito il dump, la piattaforma è accessibile su `http://localhost:8080`.

L'endpoint di ingresso è `login.php`, che richiede tre credenziali:

- Email
- Password
- AuthCode

Dopo il login, gli utenti vengono reindirizzati in base al ruolo:
- **Studente** → `prestiti.php` (pagina principale con accesso controllato al catalogo)
- **Bibliotecario** → `gestione_restituzioni.php`

---

## Credenziali di accesso di testing

#ADMIN
- email: tenerelli.francesco@panettipitagora.edu.it
- password: password123
- authCode: 215700

#STUDENTE 1:
- email: mario.rossi@panettipitagora.edu.it
- password: password123
- authCode: 215700

#STUDENTE 2:
- email: laura.bianchi@panettipitagora.edu.it
- password: password123
- authCode: 215700

#STUDENTE 3:
- email: anna.neri@panettipitagora.edu.it
- password: password123
- authCode: 215700
---