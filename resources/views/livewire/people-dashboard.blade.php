@php
    $stats = $this->stats;
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
    $card = 'rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900';
    $field = 'h-10 rounded-lg border border-zinc-200 bg-white px-3 text-sm shadow-xs outline-none transition focus:border-indigo-400 focus:ring-3 focus:ring-indigo-500/15 dark:border-zinc-700 dark:bg-zinc-900';
@endphp

<div @if ($stats['pending'] > 0) wire:poll.5s.visible @endif class="space-y-8">
    {{-- Kop --}}
    <header>
        <div>
            <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Personas</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight">Personen</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Leeftijd, geslacht en nationaliteit worden na het toevoegen op de achtergrond geschat op basis van de voornaam.
            </p>
        </div>
    </header>

    {{-- Kerncijfers --}}
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="{{ $card }}">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Personen</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums" data-stat="total">{{ $stats['total'] }}</p>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">in de database</p>
        </div>

        <div class="{{ $card }}">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Aangevuld</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums" data-stat="enriched">
                {{ $stats['enriched'] }}<span class="text-lg font-normal text-zinc-400"> / {{ $stats['total'] }}</span>
            </p>
            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ $enrichedPercentage }}%"></div>
            </div>
            <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                @if ($stats['pending'] > 0)
                    {{ $stats['pending'] }} {{ $stats['pending'] === 1 ? 'wacht' : 'wachten' }} op gegevens
                @else
                    alles is bijgewerkt
                @endif
            </p>
        </div>

        <div class="{{ $card }}">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Gemiddelde leeftijd</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums" data-stat="average-age">
                {{ $stats['average_age'] ?? '-' }}<span class="text-lg font-normal text-zinc-400">{{ $stats['average_age'] !== null ? ' jaar' : '' }}</span>
            </p>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">geschat door agify.io</p>
        </div>

        <div class="{{ $card }}">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Geslacht</p>
            <div class="mt-4 flex h-2.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                <div class="h-full bg-rose-400" style="width: {{ $genderShare($stats['female']) }}%"></div>
                <div class="h-full bg-sky-400" style="width: {{ $genderShare($stats['male']) }}%"></div>
                <div class="h-full bg-zinc-300 dark:bg-zinc-600" style="width: {{ $genderShare($stats['unknown']) }}%"></div>
            </div>
            <dl class="mt-3 grid grid-cols-3 gap-2 text-xs">
                @foreach ([['Vrouw', 'female', 'bg-rose-400'], ['Man', 'male', 'bg-sky-400'], ['Onbekend', 'unknown', 'bg-zinc-300 dark:bg-zinc-600']] as [$label, $key, $color])
                    <div>
                        <dt class="flex items-center gap-1.5 text-zinc-500 dark:text-zinc-400">
                            <span class="size-2 rounded-full {{ $color }}"></span>{{ $label }}
                        </dt>
                        <dd class="mt-0.5 font-semibold tabular-nums" data-stat="{{ $key }}">{{ $stats[$key] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- Lijst --}}
    <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-col gap-3 border-b border-zinc-200 p-4 sm:flex-row sm:items-center dark:border-zinc-800">
            <label class="relative flex-1">
                <span class="sr-only">Zoeken</span>
                <svg class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" />
                </svg>
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="Zoek op naam of e-mailadres" class="{{ $field }} w-full pl-9">
            </label>

            <div class="flex gap-3">
                <label class="flex-1 sm:flex-none">
                    <span class="sr-only">Geslacht</span>
                    <select wire:model.live="gender" class="{{ $field }} w-full pr-8">
                        <option value="">Elk geslacht</option>
                        <option value="female">Vrouw</option>
                        <option value="male">Man</option>
                        <option value="unknown">Onbekend</option>
                    </select>
                </label>

                <label class="flex-1 sm:flex-none">
                    <span class="sr-only">Status</span>
                    <select wire:model.live="status" class="{{ $field }} w-full pr-8">
                        <option value="">Elke status</option>
                        <option value="enriched">Aangevuld</option>
                        <option value="pending">Wacht op gegevens</option>
                    </select>
                </label>

                <button wire:click="export" type="button" title="Download de personen die bij de filters horen als JSON" class="inline-flex h-10 shrink-0 items-center gap-2 rounded-lg bg-zinc-900 px-3 text-sm font-medium text-white shadow-xs transition hover:bg-zinc-700 disabled:opacity-60 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200" wire:loading.attr="disabled" wire:target="export">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v11m0 0-4-4m4 4 4-4M5 19h14" />
                    </svg>
                    Exporteer
                </button>
            </div>
        </div>

        @if ($people->isEmpty())
            <div class="px-6 py-16 text-center">
                @if ($stats['total'] === 0)
                    <p class="font-medium">Nog geen personen</p>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Voeg de eerste toe vanuit de terminal:</p>
                    <code class="mt-4 inline-block rounded-lg bg-zinc-100 px-3 py-2 font-mono text-xs dark:bg-zinc-800">php artisan person:add Laura Vlasma laura@example.com</code>
                @else
                    <p class="font-medium">Geen personen gevonden</p>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Er is niemand die past bij deze zoekopdracht of filters.</p>
                    <button wire:click="resetFilters" type="button" class="mt-4 text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                        Filters wissen
                    </button>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 text-xs text-zinc-500 dark:bg-zinc-900/60 dark:text-zinc-400">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">Persoon</th>
                            <th scope="col" class="px-4 py-3 font-medium">Leeftijd</th>
                            <th scope="col" class="px-4 py-3 font-medium">Geslacht</th>
                            <th scope="col" class="px-4 py-3 font-medium">Nationaliteit</th>
                            <th scope="col" class="px-4 py-3 font-medium">Status</th>
                            <th scope="col" class="hidden px-4 py-3 font-medium md:table-cell">Toegevoegd</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($people as $person)
                            <tr wire:key="person-{{ $person->id }}" class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                <td class="px-4 py-3">
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
                                        {{ $person->estimated_age }} <span class="text-zinc-400">jaar</span>
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @if ($person->isEnriched())
                                        <span @class([
                                            'inline-flex rounded-md px-2 py-0.5 text-xs font-medium',
                                            'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' => $person->estimated_gender === 'female',
                                            'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300' => $person->estimated_gender === 'male',
                                            'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300' => $person->estimated_gender === null,
                                        ])>{{ $person->genderLabel() }}</span>
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
                                    @if ($person->isEnriched())
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 dark:text-emerald-400">
                                            <span class="size-1.5 rounded-full bg-emerald-500"></span>Aangevuld
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 dark:text-amber-400">
                                            <span class="relative flex size-1.5">
                                                <span class="absolute inline-flex size-full animate-ping rounded-full bg-amber-400 opacity-75"></span>
                                                <span class="relative inline-flex size-1.5 rounded-full bg-amber-500"></span>
                                            </span>
                                            Wacht op gegevens
                                        </span>
                                    @endif
                                </td>

                                <td class="hidden px-4 py-3 text-xs whitespace-nowrap text-zinc-500 md:table-cell dark:text-zinc-400" title="{{ $person->created_at->format('d-m-Y H:i') }}">
                                    {{ $person->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($people->hasPages())
                <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-800">
                    {{ $people->links() }}
                </div>
            @endif
        @endif
    </section>
</div>
