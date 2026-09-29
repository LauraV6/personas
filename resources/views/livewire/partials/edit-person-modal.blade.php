@php
    $original = $person->only('first_name', 'last_name', 'email');
    $inputClass = fn (string $name) => \Illuminate\Support\Arr::toCssClasses([
        $field.' w-full',
        'border-rose-400! focus:border-rose-500! focus:ring-rose-500/15! dark:border-rose-500/60!' => $errors->has("form.{$name}"),
    ]);
@endphp

<div class="fixed inset-0 m-0 z-50 flex items-end justify-center sm:items-center sm:p-4" role="dialog" aria-modal="true" aria-labelledby="edit-title" aria-describedby="edit-description"
    x-data="{ init() { document.body.style.overflow = 'hidden' }, destroy() { document.body.style.overflow = '' } }"
    x-on:keydown.escape.window="$wire.cancelEdit()">
    <div class="animate-fade-in absolute inset-0 bg-zinc-950/40 backdrop-blur-sm" wire:click="cancelEdit" aria-hidden="true"></div>

    <form wire:submit="save" novalidate
        class="animate-modal-in relative flex max-h-[92vh] w-full flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl ring-1 ring-zinc-900/5 sm:max-w-lg sm:rounded-2xl dark:bg-zinc-900 dark:ring-white/10"
        x-data="{
            original: @js($original),
            get dirty() { return Object.keys(this.original).some(key => (this.$wire.form[key] ?? '') !== this.original[key]) },
            get firstNameChanged() { return (this.$wire.form.first_name ?? '').trim() !== this.original.first_name },
        }"
        x-init="$nextTick(() => $el.querySelector('input')?.focus())">

        {{-- Kop: wie bewerk je --}}
        <div class="flex items-start gap-4 border-b border-zinc-100 px-6 pt-6 pb-5 dark:border-zinc-800">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-full text-sm font-semibold {{ $avatarColors[$person->id % count($avatarColors)] }}">
                {{ $person->initials() }}
            </span>
            <div class="min-w-0 flex-1">
                <h2 id="edit-title" class="text-lg leading-tight font-semibold tracking-tight">Persoon bewerken</h2>
                <p id="edit-description" class="mt-1 truncate text-sm text-zinc-500 dark:text-zinc-400">
                    {{ $person->fullName() }} <span class="text-zinc-300 dark:text-zinc-600">·</span> {{ $person->email }}
                </p>
            </div>
            <button wire:click="cancelEdit" type="button" aria-label="Sluiten" class="-mt-1 -mr-2 inline-flex size-8 shrink-0 items-center justify-center rounded-lg text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-900 focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:outline-none dark:hover:bg-zinc-800 dark:hover:text-white">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Velden --}}
        <div class="space-y-5 overflow-y-auto px-6 py-6">
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach (['first_name' => ['Voornaam', 'given-name'], 'last_name' => ['Achternaam', 'family-name']] as $name => [$label, $autocomplete])
                    <div>
                        <label for="form-{{ $name }}" class="block text-sm font-medium">{{ $label }}</label>
                        <input wire:model="form.{{ $name }}" id="form-{{ $name }}" type="text" autocomplete="{{ $autocomplete }}" class="{{ $inputClass($name) }} mt-1.5"
                            @error("form.{$name}") aria-invalid="true" aria-describedby="form-{{ $name }}-error" @enderror>
                        @error("form.{$name}")
                            <p id="form-{{ $name }}-error" class="mt-1.5 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div>
                <label for="form-email" class="block text-sm font-medium">E-mailadres</label>
                <div class="relative mt-1.5">
                    <svg class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                    <input wire:model="form.email" id="form-email" type="email" autocomplete="email" class="{{ $inputClass('email') }} pl-9"
                        @error('form.email') aria-invalid="true" aria-describedby="form-email-error" @enderror>
                </div>
                @error('form.email')
                    <p id="form-email-error" class="mt-1.5 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Wat er met de opgehaalde gegevens gebeurt --}}
            <div class="rounded-xl p-4 ring-1 transition-colors ring-inset"
                :class="firstNameChanged
                    ? 'bg-amber-50 ring-amber-600/20 dark:bg-amber-500/10 dark:ring-amber-400/25'
                    : 'bg-zinc-50 ring-zinc-200 dark:bg-zinc-800/50 dark:ring-zinc-700'">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-medium text-zinc-600 dark:text-zinc-300">Opgehaalde gegevens</p>
                    <p class="text-xs text-zinc-400" x-show="! firstNameChanged">op basis van "{{ $person->first_name }}"</p>
                    <p class="inline-flex items-center gap-1 text-xs font-medium text-amber-700 dark:text-amber-400" x-show="firstNameChanged" x-cloak>
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Wordt opnieuw opgehaald
                    </p>
                </div>

                <div class="mt-2.5 flex flex-wrap gap-1.5 text-xs transition" :class="firstNameChanged && 'opacity-50 line-through'">
                    @if ($person->isEnriched())
                        <span class="rounded-md bg-white px-2 py-1 font-medium ring-1 ring-zinc-200 ring-inset dark:bg-zinc-900 dark:ring-zinc-700">
                            {{ $person->estimated_age !== null ? $person->estimated_age.' jaar' : 'Leeftijd onbekend' }}
                        </span>
                        <span class="rounded-md bg-white px-2 py-1 font-medium ring-1 ring-zinc-200 ring-inset dark:bg-zinc-900 dark:ring-zinc-700">{{ $person->genderLabel() }}</span>
                        <span class="inline-flex items-center gap-1 rounded-md bg-white px-2 py-1 font-medium ring-1 ring-zinc-200 ring-inset dark:bg-zinc-900 dark:ring-zinc-700">
                            @if ($person->estimated_nationality !== null)
                                <span aria-hidden="true">{{ $person->nationalityFlag() }}</span>{{ $person->nationalityLabel() }}
                            @else
                                Nationaliteit onbekend
                            @endif
                        </span>
                    @else
                        <span class="text-zinc-500 dark:text-zinc-400">Nog geen gegevens opgehaald</span>
                    @endif
                </div>

                <p class="mt-2.5 text-xs text-amber-800 dark:text-amber-300" x-show="firstNameChanged" x-cloak>
                    Leeftijd, geslacht en nationaliteit zijn geschat op de voornaam. Na opslaan worden ze gewist en voor de nieuwe voornaam opnieuw opgehaald.
                </p>
            </div>
        </div>

        {{-- Voet met knoppen --}}
        <div class="flex flex-col-reverse gap-2 border-t border-zinc-100 bg-zinc-50/80 px-6 pt-4 pb-[max(1rem,env(safe-area-inset-bottom))] sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800 dark:bg-zinc-950/40">
            <p class="hidden text-xs text-zinc-400 sm:block">
                <kbd class="rounded border border-zinc-200 bg-white px-1.5 py-0.5 font-sans text-[11px] text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">Esc</kbd> om te sluiten
            </p>
            <div class="flex flex-col-reverse gap-2 sm:flex-row">
                <button wire:click="cancelEdit" type="button" class="{{ $secondaryButton }}">Annuleren</button>
                <button type="submit" class="{{ $primaryButton }}" :disabled="! dirty" :title="dirty ? null : 'Er is nog niets gewijzigd'">
                    <svg wire:loading wire:target="save" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                    </svg>
                    Wijzigingen opslaan
                </button>
            </div>
        </div>
    </form>
</div>
