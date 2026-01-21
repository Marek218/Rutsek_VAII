// Made with AI
# Luxer BB — Inštalácia a spustenie

Tento README obsahuje kroky pre rýchle spustenie projektu lokálne. Predpokladá sa, že pracujete na Windows (PowerShell), ale návod obsahuje aj alternatívu bez Dockeru.

---

## Požiadavky
- Git (klonovanie repozitára)
- Docker a Docker Compose (odporúčané) alebo PHP 8 + web server + MariaDB/MySQL (alternatíva)
- PowerShell (Windows) alebo bash (Linux/Mac)

---

## Rýchle spustenie (Docker — odporúčané)
Repo obsahuje `docker/docker-compose.yml`, ktorý nastaví 3 kontajnery: web (Apache + PHP), databázu (MariaDB) a Adminer (DB GUI).

1) Otvorte terminál v priečinku projektu (kde je tento README).

2) Skontrolujte súbor `docker/.env` — obsahuje premenné pre vytvorenie DB (už nastavené v repozitári).

3) Spustite Docker Compose

4) Počkajte, kým sa kontajnery nespustia. Skontrolujte ich stav:

5) Otvorte prehliadač:
- Web aplikácia: http://127.0.0.1/ (mapuje sa port 80 z kontajnera)
- Adminer (DB GUI): http://127.0.0.1:8080/

6) Prihlásenie do Admineru: použite údaje z `docker/.env`:
- Server: `db`
- Databáza: `vaiicko_db`
- Užívateľ: `vaiicko_user`
- Heslo: `dtb456`

> Pozn. MariaDB init SQL skripty sú v `docker/sql/` a budú sa automaticky spúšťať pri prvom behu kontajnera DB. Tam sú vytvorené tabuľky a počiatočné údaje.

7) Admin do aplikácie (lokálne):
- Používateľ: `admin`
- Heslo: `admin`

(Tento účet je definovaný v `Framework/Auth/DummyAuthenticator.php` a slúži na lokálne testovanie.)

## Umiestnenie uploadov a statických súborov
- Verejne prístupné súbory sú v `public/`.
- Uploady (galéria, atď.) by mali byť v `public/uploads/` (konfigurácia v `App/Configuration.php` => `UPLOAD_DIR` a `UPLOAD_URL`).
- Ak nahrávate súbory na Linux, uistite sa, že `public/uploads` má práva na zápis pre web server (napr. `chmod 0777 public/uploads` pri lokálnom testovaní).

---

## Ako pracovať s DB / galériou
- Obrázky pre galériu sú referencované v DB tabuľke `gallery` cez stĺpec `path_url` (v SQL skriptoch a v kóde: model `Gallery`).
- Pri použití Docker Compose sú SQL skripty v `docker/sql/` automaticky načítané pri prvom spustení DB kontajnera.

---

## Debug & riešenie problémov
- Ak vidíte chybu 500 alebo iné problémy, skontrolujte logy web kontajnera:

```powershell
docker-compose -f docker\docker-compose.yml logs web --tail=200
```

- Ak sa v prehliadači AJAX vracia HTML (napr. login stránka), znamená to, že požiadavka nebola autorizovaná alebo server redirectol — skontrolujte session/auth.
- PHP error log v Docker obraze môže byť v `/var/log/apache2/error.log` (závisí od image). Použite `docker exec` a `tail -f` ak potrebujete živé logy.

---

## Úprava prihlasovacích údajov administrátora
Admin účet (lokálny test) je definovaný v `Framework/Auth/DummyAuthenticator.php`:
- Užívateľ: `admin`
- Heslo: `admin`

Ak chcete zmeniť heslo, upravte `PASSWORD_HASH` alebo implementujte iný `AUTH_CLASS`.

---

## Časté príčiny problémov
- Nemenené DB konštanty v `App/Configuration.php` pri spustení bez Dockeru.
- Chýbajúce práva zápisu do `public/uploads`.
- Staré cacheované CSS/JS v prehliadači — skúste Ctrl+F5.

---