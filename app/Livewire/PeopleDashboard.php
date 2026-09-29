<?php

namespace App\Livewire;

use App\Models\Person;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PeopleDashboard extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $gender = '';

    #[Url(except: '')]
    public string $status = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'gender', 'status'])) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'gender', 'status');
        $this->resetPage();
    }

    /**
     * Kerncijfers voor de kaarten bovenaan, in één query.
     *
     * @return array{total: int, enriched: int, pending: int, average_age: ?int, female: int, male: int, unknown: int}
     */
    #[Computed]
    public function stats(): array
    {
        $row = Person::query()->toBase()
            ->selectRaw('count(*) as total')
            ->selectRaw('count(enriched_at) as enriched')
            ->selectRaw('avg(estimated_age) as average_age')
            ->selectRaw("sum(case when estimated_gender = 'female' then 1 else 0 end) as female")
            ->selectRaw("sum(case when estimated_gender = 'male' then 1 else 0 end) as male")
            ->first();

        $enriched = (int) $row->enriched;

        return [
            'total' => (int) $row->total,
            'enriched' => $enriched,
            'pending' => (int) $row->total - $enriched,
            'average_age' => $row->average_age !== null ? (int) round($row->average_age) : null,
            'female' => (int) $row->female,
            'male' => (int) $row->male,
            'unknown' => $enriched - (int) $row->female - (int) $row->male,
        ];
    }

    public function render(): View
    {
        $people = Person::query()
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
            ->latest()
            ->latest('id')
            ->paginate(10);

        return view('livewire.people-dashboard', ['people' => $people]);
    }
}
