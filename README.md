# Personas

Kleine Laravel-applicatie voor het beheren van personen. Een persoon wordt via de CLI toegevoegd. Daarna haalt een queue-job op de achtergrond de geschatte leeftijd ([agify.io](https://agify.io)) en het geschatte geslacht ([genderize.io](https://genderize.io)) op, op basis van de voornaam, en slaat die op bij de persoon.

## Installatie

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
```

## Gebruik

Persoon toevoegen (argumenten weglaten om ze interactief in te vullen):

```bash
php artisan person:add Laura Vlasma laura@example.com
```

Queue-worker starten, zodat de gegevens worden opgehaald:

```bash
php artisan queue:work
```

Overzicht bekijken:

```bash
php artisan person:list
```

## Opzet

| Onderdeel | Bestand |
|---|---|
| Tabel `people` | `database/migrations/*_create_people_table.php` |
| Model | `app/Models/Person.php` |
| CLI-commando's | `app/Console/Commands/AddPerson.php`, `ListPeople.php` |
| Ophalen van leeftijd en geslacht | `app/Jobs/EnrichPerson.php` |

- De job doet beide API-verzoeken parallel via `Http::pool`.
- Faalt een verzoek (bijvoorbeeld door de rate limit), dan probeert de queue het tot 3 keer opnieuw, met 10, 30 en 60 seconden wachttijd.
- De gratis versie van beide API's staat 100 namen per dag toe.
- Bij een onbekende naam blijven leeftijd en geslacht leeg, maar wordt `enriched_at` wel gezet.

## Tests en codestijl

```bash
php artisan test      # Pest
./vendor/bin/pint     # Laravel Pint
```

De API's worden in de tests gefaked, er gaan geen echte verzoeken uit.

## Branches

Git Flow: `main` bevat wat live staat, `develop` is de werkbranch. Nieuwe features gaan via `feature/*` naar `develop`.
