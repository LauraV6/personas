<?php

namespace App\Livewire;

use App\Actions\ExportPeopleAsJson;
use App\Actions\UpdatePerson;
use App\Models\Person;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PeopleDashboard extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $gender = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: 'created')]
    public string $sort = 'created';

    #[Url(except: 'desc')]
    public string $direction = 'desc';

    /** De persoon die in het bewerkvenster open staat. */
    public ?int $editingId = null;

    /** @var array{first_name: string, last_name: string, email: string} */
    public array $form = ['first_name' => '', 'last_name' => '', 'email' => ''];

    /** De persoon waarvoor het verwijdervenster open staat. */
    public ?int $deletingId = null;

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'gender', 'status'])) {
            $this->resetPage();
        }
    }

    public function setStatus(string $status): void
    {
        $this->status = in_array($status, ['enriched', 'pending']) ? $status : '';
        $this->resetPage();
    }

    /**
     * Klik je op dezelfde kolom, dan draait de volgorde om. Een nieuwe kolom
     * begint oplopend, behalve "toegevoegd", die begint bij de nieuwste.
     */
    public function sortBy(string $column): void
    {
        if (! in_array($column, ['name', 'age', 'created'])) {
            return;
        }

        if ($this->sort === $column) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $column;
            $this->direction = $column === 'created' ? 'desc' : 'asc';
        }

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'gender', 'status');
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        $person = Person::find($id);

        if ($person === null) {
            $this->notify('Deze persoon bestaat niet meer.', 'error');

            return;
        }

        $this->resetErrorBag();
        $this->form = $person->only('first_name', 'last_name', 'email');
        $this->editingId = $person->id;
    }

    public function cancelEdit(): void
    {
        $this->resetErrorBag();
        $this->reset('editingId', 'form');
    }

    public function save(UpdatePerson $updatePerson): void
    {
        $person = Person::find($this->editingId);

        if ($person === null) {
            $this->cancelEdit();
            $this->notify('Deze persoon bestaat niet meer.', 'error');

            return;
        }

        $oldFirstName = $person->first_name;

        try {
            $updatePerson->handle($person, $this->form);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->addError("form.{$field}", $messages[0]);
            }

            return;
        }

        $this->cancelEdit();
        $this->notify($person->first_name !== $oldFirstName
            ? "{$person->fullName()} is bijgewerkt. De gegevens worden opnieuw opgehaald."
            : "{$person->fullName()} is bijgewerkt.");
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = Person::whereKey($id)->exists() ? $id : null;
    }

    public function cancelDelete(): void
    {
        $this->reset('deletingId');
    }

    public function delete(): void
    {
        $person = Person::find($this->deletingId);
        $this->reset('deletingId');

        if ($person === null) {
            $this->notify('Deze persoon bestaat niet meer.', 'error');

            return;
        }

        $person->delete();
        $this->notify("{$person->fullName()} is verwijderd.");
    }

    #[Computed]
    public function deletingPerson(): ?Person
    {
        return $this->deletingId !== null ? Person::find($this->deletingId) : null;
    }

    /**
     * Kerncijfers voor de kaarten bovenaan, in één query.
     *
     * @return array{total: int, today: int, enriched: int, pending: int, average_age: ?int, female: int, male: int, unknown: int}
     */
    #[Computed]
    public function stats(): array
    {
        $row = Person::query()->toBase()
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(case when created_at >= ? then 1 else 0 end) as today', [today()->toDateTimeString()])
            ->selectRaw('count(enriched_at) as enriched')
            ->selectRaw('avg(estimated_age) as average_age')
            ->selectRaw("sum(case when estimated_gender = 'female' then 1 else 0 end) as female")
            ->selectRaw("sum(case when estimated_gender = 'male' then 1 else 0 end) as male")
            ->first();

        $enriched = (int) $row->enriched;

        return [
            'total' => (int) $row->total,
            'today' => (int) $row->today,
            'enriched' => $enriched,
            'pending' => (int) $row->total - $enriched,
            'average_age' => $row->average_age !== null ? (int) round($row->average_age) : null,
            'female' => (int) $row->female,
            'male' => (int) $row->male,
            'unknown' => $enriched - (int) $row->female - (int) $row->male,
        ];
    }

    /**
     * De drie meest voorkomende nationaliteiten.
     *
     * @return list<array{code: string, name: string, flag: string, count: int}>
     */
    #[Computed]
    public function topCountries(): array
    {
        return Person::query()->toBase()
            ->whereNotNull('estimated_nationality')
            ->selectRaw('estimated_nationality as code, count(*) as count')
            ->groupBy('estimated_nationality')
            ->orderByDesc('count')
            ->orderBy('code')
            ->limit(3)
            ->get()
            ->map(fn (object $row) => [
                'code' => $row->code,
                'name' => Person::countryName($row->code),
                'flag' => Person::countryFlag($row->code),
                'count' => (int) $row->count,
            ])
            ->all();
    }

    /**
     * Aantal personen per leeftijdsgroep, voor het histogram op de leeftijdskaart.
     *
     * @return array<string, int>
     */
    #[Computed]
    public function ageGroups(): array
    {
        $groups = ['<20' => 0, '20' => 0, '30' => 0, '40' => 0, '50' => 0, '60' => 0, '70+' => 0];

        foreach (Person::whereNotNull('estimated_age')->pluck('estimated_age') as $age) {
            $key = match (true) {
                $age < 20 => '<20',
                $age >= 70 => '70+',
                default => (string) (intdiv($age, 10) * 10),
            };

            $groups[$key]++;
        }

        return $groups;
    }

    /**
     * Downloadt de personen die bij de huidige zoekopdracht en filters horen als JSON.
     */
    public function export(ExportPeopleAsJson $export): StreamedResponse
    {
        $json = $export->handle($this->filteredPeople()->get());

        return response()->streamDownload(
            function () use ($json) {
                echo $json;
            },
            'personen-'.now()->format('Y-m-d').'.json',
            ['Content-Type' => 'application/json'],
        );
    }

    public function render(): View
    {
        $people = $this->filteredPeople()->paginate(10);

        // Na een verwijdering kan de laatste pagina leeg zijn, spring dan terug.
        if ($people->isEmpty() && $people->currentPage() > 1) {
            $this->setPage($people->lastPage());
            $people = $this->filteredPeople()->paginate(10);
        }

        return view('livewire.people-dashboard', ['people' => $people]);
    }

    /**
     * @return Builder<Person>
     */
    protected function filteredPeople(): Builder
    {
        return Person::query()
            ->when($this->search !== '', function (Builder $query) {
                // Elk woord moet in de voornaam, achternaam of het e-mailadres voorkomen.
                foreach (preg_split('/\s+/', trim($this->search)) as $term) {
                    $query->where(fn (Builder $query) => $query
                        ->where('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%"));
                }
            })
            ->when($this->gender === 'unknown', fn (Builder $query) => $query->whereNotNull('enriched_at')->whereNull('estimated_gender'))
            ->when(in_array($this->gender, ['female', 'male']), fn (Builder $query) => $query->where('estimated_gender', $this->gender))
            ->when($this->status === 'enriched', fn (Builder $query) => $query->whereNotNull('enriched_at'))
            ->when($this->status === 'pending', fn (Builder $query) => $query->whereNull('enriched_at'))
            ->when($this->sort === 'name', fn (Builder $query) => $query->orderBy('last_name', $this->direction)->orderBy('first_name', $this->direction))
            ->when($this->sort === 'age', fn (Builder $query) => $query->orderBy('estimated_age', $this->direction))
            ->when($this->sort === 'created', fn (Builder $query) => $query->orderBy('created_at', $this->direction))
            ->orderBy('id', $this->direction);
    }

    protected function notify(string $message, string $type = 'success'): void
    {
        $this->dispatch('notify', message: $message, type: $type);
    }
}
