# Applicazione mvp per autenticazione con laravel sanctum in laravel

## Strumenti utilizzati
- framework Laravel 8 e sanctum
- ambiente di sviluppo devilbox 1.9.3 https://github.com/cytopia/devilbox
    - php ver. 8.0
    - apache ver. 2.4
    - configurato con accesso diretto a tutte le porte dei servizi
- database sqlite - gia presente e configurato nel file .env.example

## Comandi per attivare il progetto
- docker-compose up php
    - lancia direttamente il comando per attivare la php unit
    - al suo interno commentati sono presenti altri due comandi:
        - uno per lanciare un web server in modo da poter testare le api con postman o simili
        - per aprire una shell direttamente dentro alla container

## Route create dentro al middleware
### (escluse dal middleware dentro il controller le route register e login)

- POST /api/auth/logout - per eseguire il logout tramite il token
- GET /api/auth/me - per ottenere i dati utente tramite il token

## Route esterne al middleware
- POST /api/auth/register - per registrare un utente
- POST /api/auth/login - per eseguire il cookie di login

## Route di test
- GET /api/test/open - api di test esterna al middleware
- GET /api/test/closed - api di test interna al middleware

#TODO finire
## PHP UNIT
- per eseguire la php unit lanciare il comando ./vendor/bin/phpunit
- la unit verifica:
    - le API jwt:
        - creazione utente
        - ricreazione stesso utente
        - login
        - ottenere i dati utente con il token
        - collegarmi con un token errato
        - fare il logout
    - la API di test:
        - test di accesso alla api aperta
        - test di accesso alla api solo per utenti registrati

## Istruzioni per generare un nuovo progetto da zero
- TODO
