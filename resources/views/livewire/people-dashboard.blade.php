@php
    $stats = $this->stats;
    $countries = $this->topCountries;
    $ageGroups = $this->ageGroups;
    $maxAgeGroup = max(1, ...array_values($ageGroups));
    $enrichedPercentage = $stats['total'] > 0 ? round($stats['enriched'] / $stats['total'] * 100) : 0;
    $genderShare = fn (int $count) => $stats['enriched'] > 0 ? $count / $stats['enriched'] * 100 : 0;

    $avatarColors = [
        'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300',
        'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
        'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
        'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
    ];
    $genderBadge = [
        'female' => 'bg-rose-50 text-rose-700 ring-rose-600/15 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-400/20',
        'male' => 'bg-sky-50 text-sky-700 ring-sky-600/15 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-400/20',
        'unknown' => 'bg-zinc-50 text-zinc-600 ring-zinc-500/15 dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-400/20',
    ];
    $tabs = [
        '' => ['Alle', $stats['total']],
        'enriched' => ['Aangevuld', $stats['enriched']],
        'pending' => ['Wachtend', $stats['pending']],
    ];
    if ($stats['failed'] > 0 || $status === 'failed') {
        $tabs['failed'] = ['Mislukt', $stats['failed']];
    }

    $card = 'relative overflow-hidden rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm shadow-zinc-900/[0.03] dark:border-zinc-800 dark:bg-zinc-900 dark:shadow-none';
    $iconBox = 'flex size-9 items-center justify-center rounded-xl';
    $field = 'h-10 rounded-xl border border-zinc-200 bg-white px-3 text-sm shadow-xs outline-none transition placeholder:text-zinc-400 hover:border-zinc-300 focus:border-indigo-400 dark:hover:border-zinc-600 focus:ring-4 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-950 dark:focus:border-indigo-500';
    $loadingTargets = 'search,gender,setStatus,sortBy,resetFilters,gotoPage,nextPage,previousPage,delete';
    $buttonBase = 'inline-flex h-10 items-center justify-center gap-2 rounded-xl px-4 text-sm font-medium transition focus-visible:ring-4 focus-visible:outline-none enabled:active:scale-[0.98] disabled:opacity-50';
    $primaryButton = $buttonBase.' bg-indigo-600 text-white shadow-sm enabled:hover:bg-indigo-500 focus-visible:ring-indigo-500/30 dark:bg-indigo-500 dark:enabled:hover:bg-indigo-400';
    $secondaryButton = $buttonBase.' bg-white text-zinc-700 ring-1 ring-zinc-200 ring-inset hover:bg-zinc-50 hover:text-zinc-900 focus-visible:ring-zinc-900/10 dark:bg-zinc-800 dark:text-zinc-200 dark:ring-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-white';
    $dangerButton = $buttonBase.' bg-rose-600 text-white shadow-sm hover:bg-rose-500 focus-visible:ring-rose-500/30';
@endphp

<div @if ($stats['pending'] > 0 && $editingId === null && $deletingId === null) wire:poll.5s.visible @endif class="space-y-8">
    {{-- Kop --}}
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-semibold tracking-tight">Personen</h1>

                @if ($stats['pending'] > 0)
                    <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 ring-1 ring-amber-600/15 ring-inset dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-400/20">
                        <span class="size-2 rounded-full bg-amber-500"></span>
                        {{ $stats['pending'] }} {{ $stats['pending'] === 1 ? 'wacht' : 'wachten' }} op gegevens
                    </span>
                @endif

                @if ($stats['failed'] > 0)
                    <button wire:click="setStatus('failed')" type="button" title="Toon de personen waarbij het ophalen mislukte" class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-medium text-rose-700 ring-1 ring-rose-600/15 transition ring-inset hover:bg-rose-100 focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:outline-none dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-400/20 dark:hover:bg-rose-500/20">
                        <span class="size-2 rounded-full bg-rose-500"></span>
                        {{ $stats['failed'] }} mislukt
                    </button>
                @endif
            </div>
            <p class="mt-1.5 max-w-xl text-sm text-zinc-500 dark:text-zinc-400">
                Leeftijd, geslacht en nationaliteit worden na het toevoegen op de achtergrond geschat op basis van de voornaam.
            </p>
        </div>

        <button wire:click="export" wire:loading.attr="disabled" wire:target="export" type="button" title="Download de personen die bij je filters horen als JSON" class="inline-flex h-10 shrink-0 items-center gap-2 self-start rounded-xl bg-indigo-600 px-4 text-sm font-medium text-white shadow-sm shadow-indigo-600/20 transition hover:-translate-y-px hover:bg-indigo-500 hover:shadow-md hover:shadow-indigo-600/25 focus-visible:ring-4 active:translate-y-0 active:scale-[0.98] focus-visible:ring-indigo-500/30 focus-visible:outline-none disabled:opacity-60 sm:self-auto dark:bg-indigo-500 dark:shadow-none dark:hover:bg-indigo-400">
            <svg wire:loading.remove wire:target="export" class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            <svg wire:loading wire:target="export" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
            </svg>
            Exporteer JSON
        </button>
    </header>

    {{-- Kerncijfers --}}
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Personen --}}
        <div class="{{ $card }}">
            <div class="flex items-start justify-between">
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Personen</p>
                <span class="{{ $iconBox }} bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </span>
            </div>
            <div class="mt-1 flex items-baseline gap-2">
                <p class="text-3xl font-semibold tracking-tight tabular-nums" data-stat="total">{{ $stats['total'] }}</p>
                @if ($stats['today'] > 0)
                    <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">+{{ $stats['today'] }} vandaag</span>
                @endif
            </div>
            <div class="mt-4 flex flex-wrap gap-1.5 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                @forelse ($countries as $country)
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-50 px-2 py-1 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300" title="{{ $country['name'] }}">
                        <span class="text-sm leading-none">{{ $country['flag'] }}</span>
                        <span class="font-medium tabular-nums">{{ $country['count'] }}</span>
                    </span>
                @empty
                    <span class="text-xs text-zinc-400">Nog geen nationaliteiten</span>
                @endforelse
            </div>
        </div>

        {{-- Aangevuld --}}
        <div class="{{ $card }}">
            <div class="flex items-start justify-between">
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Aangevuld</p>
                <span class="{{ $iconBox }} bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
                    </svg>
                </span>
            </div>
            <p class="mt-1 text-3xl font-semibold tracking-tight tabular-nums" data-stat="enriched">
                {{ $stats['enriched'] }}<span class="text-lg font-normal text-zinc-400"> / {{ $stats['total'] }}</span>
            </p>
            <div class="mt-4 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-zinc-500 dark:text-zinc-400">
                        @if ($stats['pending'] > 0 || $stats['failed'] > 0)
                            {{ collect([
                                $stats['pending'] > 0 ? $stats['pending'].' '.($stats['pending'] === 1 ? 'wacht' : 'wachten') : null,
                                $stats['failed'] > 0 ? $stats['failed'].' mislukt' : null,
                            ])->filter()->join(', ') }}
                        @else
                            Alles is bijgewerkt
                        @endif
                    </span>
                    <span class="font-medium tabular-nums">{{ $enrichedPercentage }}%</span>
                </div>
                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <div class="h-full rounded-full bg-linear-to-r from-emerald-400 to-emerald-500 transition-all duration-700" style="width: {{ $enrichedPercentage }}%"></div>
                </div>
            </div>
        </div>

        {{-- Gemiddelde leeftijd --}}
        <div class="{{ $card }}">
            <div class="flex items-start justify-between">
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Gemiddelde leeftijd</p>
                <span class="{{ $iconBox }} bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </span>
            </div>
            <p class="mt-1 text-3xl font-semibold tracking-tight tabular-nums" data-stat="average-age">
                {{ $stats['average_age'] ?? '-' }}<span class="text-lg font-normal text-zinc-400">{{ $stats['average_age'] !== null ? ' jaar' : '' }}</span>
            </p>
            <div class="mt-4 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                <div class="flex h-8 items-end gap-1" role="img" aria-label="Verdeling van de leeftijden">
                    @foreach ($ageGroups as $group => $count)
                        <div class="h-full flex-1 rounded-sm bg-zinc-100 dark:bg-zinc-800" title="{{ $group === '<20' || $group === '70+' ? $group : $group.'-'.($group + 9) }} jaar: {{ $count }}">
                            <div class="flex h-full items-end">
                                <div class="w-full rounded-sm bg-amber-400 transition-all duration-700 dark:bg-amber-500" style="height: {{ $count / $maxAgeGroup * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-1 flex gap-1 text-center text-[10px] text-zinc-400 tabular-nums">
                    @foreach (array_keys($ageGroups) as $group)
                        <span class="flex-1">{{ $group }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Geslacht --}}
        <div class="{{ $card }}">
            <div class="flex items-start justify-between">
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Geslacht</p>
                <span class="{{ $iconBox }} bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3 flex h-2.5 gap-0.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                <div class="h-full rounded-full bg-rose-400 transition-all duration-700" style="width: {{ $genderShare($stats['female']) }}%"></div>
                <div class="h-full rounded-full bg-sky-400 transition-all duration-700" style="width: {{ $genderShare($stats['male']) }}%"></div>
                <div class="h-full rounded-full bg-zinc-300 transition-all duration-700 dark:bg-zinc-600" style="width: {{ $genderShare($stats['unknown']) }}%"></div>
            </div>
            <dl class="mt-4 grid grid-cols-3 gap-2 border-t border-zinc-100 pt-3 text-xs dark:border-zinc-800">
                @foreach ([['Vrouw', 'female', 'bg-rose-400'], ['Man', 'male', 'bg-sky-400'], ['Onbekend', 'unknown', 'bg-zinc-300 dark:bg-zinc-600']] as [$label, $key, $color])
                    <div>
                        <dt class="flex items-center gap-1.5 text-zinc-500 dark:text-zinc-400">
                            <span class="size-2 rounded-full {{ $color }}"></span>{{ $label }}
                        </dt>
                        <dd class="mt-0.5 text-base font-semibold tabular-nums" data-stat="{{ $key }}">{{ $stats[$key] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- Lijst --}}
    <section class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03] dark:border-zinc-800 dark:bg-zinc-900 dark:shadow-none">
        <div class="flex flex-col gap-3 border-b border-zinc-200/80 p-3 lg:flex-row lg:items-center lg:justify-between dark:border-zinc-800">
            {{-- Status als tabbladen --}}
            <nav @class([
                'grid gap-1 rounded-xl bg-zinc-100/80 p-1 sm:inline-flex sm:self-start lg:self-auto dark:bg-zinc-800/60',
                count($tabs) > 3 ? 'grid-cols-2' : 'grid-cols-3',
            ]) aria-label="Status">
                @foreach ($tabs as $value => [$label, $count])
                    <button wire:click="setStatus('{{ $value }}')" type="button" aria-pressed="{{ $status === $value ? 'true' : 'false' }}" @class([
                        'inline-flex items-center justify-center gap-1.5 rounded-lg px-2 py-1.5 text-sm font-medium transition focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:outline-none sm:gap-2 sm:px-3',
                        'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' => $status === $value,
                        'text-zinc-500 hover:bg-white/60 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-700/50 dark:hover:text-white' => $status !== $value,
                    ])>
                        {{ $label }}
                        <span @class([
                            'rounded-md px-1.5 text-xs tabular-nums',
                            'bg-zinc-100 text-zinc-600 dark:bg-zinc-600 dark:text-zinc-200' => $status === $value,
                            'bg-zinc-200/70 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400' => $status !== $value,
                        ])>{{ $count }}</span>
                    </button>
                @endforeach
            </nav>

            <div class="flex flex-col gap-2 sm:flex-row">
                <label class="relative sm:w-72">
                    <span class="sr-only">Zoeken</span>
                    <svg class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Zoek op naam of e-mailadres" class="{{ $field }} w-full pl-9">
                </label>

                <label>
                    <span class="sr-only">Geslacht</span>
                    <select wire:model.live="gender" class="{{ $field }} w-full pr-8 sm:w-auto">
                        <option value="">Elk geslacht</option>
                        <option value="female">Vrouw</option>
                        <option value="male">Man</option>
                        <option value="unknown">Onbekend</option>
                    </select>
                </label>
            </div>
        </div>

        @if ($people->isEmpty())
            <div class="px-6 py-20 text-center">
                <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400 dark:bg-zinc-800">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </span>
                @if ($stats['total'] === 0)
                    <p class="mt-4 font-medium">Nog geen personen</p>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Voeg de eerste toe vanuit de terminal:</p>
                    <code class="mt-4 inline-block rounded-lg bg-zinc-100 px-3 py-2 font-mono text-xs dark:bg-zinc-800">php artisan person:add Laura Vlasma laura@example.com</code>
                @else
                    <p class="mt-4 font-medium">Geen personen gevonden</p>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Er is niemand die past bij deze zoekopdracht of filters.</p>
                    <button wire:click="resetFilters" type="button" class="mt-4 rounded-lg px-3 py-1.5 text-sm font-medium text-indigo-600 transition hover:bg-indigo-50 focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:outline-none dark:text-indigo-400 dark:hover:bg-indigo-500/10">
                        Filters wissen
                    </button>
                @endif
            </div>
        @else
            <div wire:loading.delay.class="opacity-50" wire:target="{{ $loadingTargets }}" class="transition-opacity">
                {{-- Tabel vanaf tabletbreedte --}}
                <table class="hidden w-full text-left text-sm md:table">
                    <thead class="border-b border-zinc-200/80 text-xs text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                        <tr>
                            <x-sort-header column="name" :sort="$sort" :direction="$direction" class="pl-5">Persoon</x-sort-header>
                            <x-sort-header column="age" :sort="$sort" :direction="$direction">Leeftijd</x-sort-header>
                            <th scope="col" class="px-4 py-3 font-medium">Geslacht</th>
                            <th scope="col" class="px-4 py-3 font-medium">Nationaliteit</th>
                            <th scope="col" class="px-4 py-3 font-medium">Status</th>
                            <x-sort-header column="created" :sort="$sort" :direction="$direction" class="text-right">Toegevoegd</x-sort-header>
                            <th scope="col" class="py-3 pr-5 pl-2"><span class="sr-only">Acties</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($people as $person)
                            <tr wire:key="row-{{ $person->id }}" class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                                <td class="py-3 pr-4 pl-5">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-semibold {{ $avatarColors[$person->id % count($avatarColors)] }}">
                                            {{ $person->initials() }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate font-medium">{{ $person->fullName() }}</p>
                                            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $person->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap tabular-nums">
                                    @if ($person->estimated_age !== null)
                                        <span class="font-medium">{{ $person->estimated_age }}</span> <span class="text-zinc-400">jaar</span>
                                    @elseif ($person->isEnriched())
                                        <span class="text-zinc-400">Onbekend</span>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @if ($person->isEnriched())
                                        <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $genderBadge[$person->estimated_gender ?? 'unknown'] }}">{{ $person->genderLabel() }}</span>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($person->estimated_nationality !== null)
                                        <span class="inline-flex items-center gap-2">
                                            <span class="text-base leading-none" aria-hidden="true">{{ $person->nationalityFlag() }}</span>
                                            {{ $person->nationalityLabel() }}
                                        </span>
                                    @elseif ($person->isEnriched())
                                        <span class="text-zinc-400">Onbekend</span>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @include('livewire.partials.person-status', ['person' => $person])
                                </td>

                                <td class="px-4 py-3 text-right text-xs whitespace-nowrap text-zinc-500 dark:text-zinc-400" title="{{ $person->created_at->format('d-m-Y H:i') }}">
                                    {{ $person->created_at->diffForHumans() }}
                                </td>

                                <td class="py-3 pr-5 pl-2">
                                    @include('livewire.partials.person-actions', ['person' => $person])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Kaarten op mobiel --}}
                <ul class="divide-y divide-zinc-100 md:hidden dark:divide-zinc-800">
                    @foreach ($people as $person)
                        <li wire:key="card-{{ $person->id }}" class="p-4">
                            <div class="flex items-start gap-3">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold {{ $avatarColors[$person->id % count($avatarColors)] }}">
                                    {{ $person->initials() }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="truncate font-medium">{{ $person->fullName() }}</p>
                                        @include('livewire.partials.person-status', ['person' => $person])
                                    </div>
                                    <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $person->email }}</p>

                                    <div class="mt-2.5 flex flex-wrap items-center gap-1.5 text-xs">
                                        @if ($person->isEnriched())
                                            @if ($person->estimated_age !== null)
                                                <span class="rounded-md bg-zinc-100 px-2 py-0.5 font-medium tabular-nums dark:bg-zinc-800">{{ $person->estimated_age }} jaar</span>
                                            @endif
                                            <span class="rounded-md px-2 py-0.5 font-medium ring-1 ring-inset {{ $genderBadge[$person->estimated_gender ?? 'unknown'] }}">{{ $person->genderLabel() }}</span>
                                            @if ($person->estimated_nationality !== null)
                                                <span class="inline-flex items-center gap-1 rounded-md bg-zinc-100 px-2 py-0.5 dark:bg-zinc-800">
                                                    <span aria-hidden="true">{{ $person->nationalityFlag() }}</span>{{ $person->nationalityLabel() }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-zinc-400">Nog geen gegevens</span>
                                        @endif

                                        <div class="ml-auto -mr-1.5">
                                            @include('livewire.partials.person-actions', ['person' => $person])
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if ($people->hasPages())
                <div class="border-t border-zinc-200/80 px-4 py-3 dark:border-zinc-800">
                    {{ $people->links() }}
                </div>
            @endif
        @endif
    </section>

    {{-- Bewerkvenster --}}
    @if ($this->editingPerson !== null)
        @include('livewire.partials.edit-person-modal', ['person' => $this->editingPerson])
    @endif

    {{-- Verwijdervenster --}}
    @if ($this->deletingPerson !== null)
        <div class="fixed inset-0 m-0 z-50 flex items-end justify-center p-4 sm:items-center" role="alertdialog" aria-modal="true" aria-labelledby="delete-title" aria-describedby="delete-description" x-data="{ init() { document.body.style.overflow = 'hidden' }, destroy() { document.body.style.overflow = '' } }" x-on:keydown.escape.window="$wire.cancelDelete()">
            <div class="animate-fade-in absolute inset-0 bg-zinc-950/40 backdrop-blur-sm" wire:click="cancelDelete" aria-hidden="true"></div>

            <div class="animate-modal-in relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-zinc-900/5 dark:bg-zinc-900 dark:ring-white/10">
                <div class="flex gap-4">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </span>
                    <div>
                        <h2 id="delete-title" class="text-lg font-semibold tracking-tight">{{ $this->deletingPerson->fullName() }} verwijderen?</h2>
                        <p id="delete-description" class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">De persoon en de opgehaalde gegevens worden definitief verwijderd. Dit kun je niet ongedaan maken.</p>
                    </div>
                </div>

                <div class="mt-8 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button wire:click="cancelDelete" type="button" class="{{ $secondaryButton }}" x-init="$nextTick(() => $el.focus())">Annuleren</button>
                    <button wire:click="delete" wire:loading.attr="disabled" wire:target="delete" type="button" class="{{ $dangerButton }}">Verwijderen</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Meldingen --}}
    <div class="pointer-events-none fixed inset-x-0 bottom-0 z-[60] m-0 flex flex-col items-center gap-2 p-4 sm:items-end sm:p-6" aria-live="polite"
        x-data="{ toasts: [], add(detail) { const id = Date.now() + Math.random(); this.toasts.push({ id, ...detail }); setTimeout(() => this.remove(id), 4000) }, remove(id) { this.toasts = this.toasts.filter(t => t.id !== id) } }"
        x-on:notify.window="add($event.detail)">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-2 opacity-0" x-transition:leave="transition duration-150 ease-in" x-transition:leave-end="opacity-0"
                class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl bg-white p-4 text-sm shadow-lg ring-1 ring-zinc-900/5 dark:bg-zinc-800 dark:ring-white/10">
                <svg x-show="toast.type !== 'error'" class="size-5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <svg x-show="toast.type === 'error'" class="size-5 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <p class="flex-1 pt-px" x-text="toast.message"></p>
                <button x-on:click="remove(toast.id)" type="button" aria-label="Melding sluiten" class="-m-1 inline-flex size-6 items-center justify-center rounded-md text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-900 dark:hover:bg-zinc-700 dark:hover:text-white">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </template>
    </div>
</div>
