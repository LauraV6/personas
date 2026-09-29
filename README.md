# Personas

Kleine Laravel-applicatie voor het beheren van personen. Een persoon wordt via de CLI toegevoegd. Daarna haalt een queue-job op de achtergrond de geschatte leeftijd ([agify.io](https://agify.io)), het geschatte geslacht ([genderize.io](https://genderize.io)) en de geschatte nationaliteit ([nationalize.io](https://nationalize.io)) op, op basis van de voornaam, en slaat die op bij de persoon.

## Installatie

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
npm run build
```

Voorbeelddata, zonder de API's aan te roepen:

```bash
php artisan db:seed
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

Overzicht bekijken in de terminal:

```bash
php artisan person:list
```

Bijwerken en verwijderen:

```bash
php artisan person:update 1 --email=laura@voorbeeld.nl   # zonder opties vraagt hij de nieuwe waarden
php artisan person:delete 1                              # vraagt om bevestiging, --force slaat dat over
```

Wijzig je de voornaam, dan worden leeftijd, geslacht en nationaliteit gewist en opnieuw opgehaald, want die zijn op de voornaam gebaseerd. Bij een andere achternaam of een ander e-mailadres blijven ze staan.

Exporteren als JSON, naar de terminal of naar een bestand:

```bash
php artisan person:export
php artisan person:export --output=personen.json
```

## Dashboard

```bash
php artisan serve
```

Op http://127.0.0.1:8000 staat een dashboard met kerncijfers (aantal personen, hoeveel er aangevuld zijn, gemiddelde leeftijd, verdeling van het geslacht) en de lijst met personen. Je kunt zoeken op naam en e-mailadres, filteren op geslacht en status, en sorteren op naam, leeftijd en datum. Met de knop Exporteer JSON download je de personen die bij je filters horen als JSON. Per rij kun je een persoon bewerken of verwijderen. Zolang er personen op hun gegevens wachten, ververst de pagina zich elke 5 seconden, zodat je de queue-job live ziet binnenkomen.

## Opzet

| Onderdeel | Bestand |
|---|---|
| Tabel `people` | `database/migrations/*_create_people_table.php` |
| Model | `app/Models/Person.php` |
| CLI-commando's | `app/Console/Commands/` (`person:add`, `person:list`, `person:update`, `person:delete`, `person:export`) |
| Toevoegen en bijwerken, met gedeelde validatie | `app/Actions/CreatePerson.php`, `app/Actions/UpdatePerson.php` |
| Ophalen van leeftijd, geslacht en nationaliteit | `app/Jobs/EnrichPerson.php` |
| JSON-export | `app/Actions/ExportPeopleAsJson.php`, `app/Http/Resources/PersonResource.php` |
| Dashboard (Livewire) | `app/Livewire/PeopleDashboard.php`, `resources/views/livewire/people-dashboard.blade.php` |

- De job doet de drie API-verzoeken parallel via `Http::pool`. Van nationalize.io bewaren we het land met de grootste kans.
- Faalt een verzoek (bijvoorbeeld door de rate limit), dan probeert de queue het tot 3 keer opnieuw, met 10, 30 en 60 seconden wachttijd.
- De gratis versie van de API's staat 10 namen per dag toe.
- Wordt een persoon verwijderd terwijl zijn job nog in de wachtrij staat, dan verdwijnt de job zonder fout (`#[DeleteWhenMissingModels]`).
- Bij een onbekende naam blijven leeftijd, geslacht en nationaliteit leeg, maar wordt `enriched_at` wel gezet.

## Tests en codestijl

```bash
php artisan test      # Pest
./vendor/bin/pint     # Laravel Pint
```

De API's worden in de tests gefaked, er gaan geen echte verzoeken uit.

## Branches

Git Flow: `main` bevat wat live staat, `develop` is de werkbranch. Nieuwe features gaan via `feature/*` naar `develop`.
