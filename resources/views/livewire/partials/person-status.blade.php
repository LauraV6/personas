@if ($person->isEnriched())
    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/15 ring-inset dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-400/20">
        <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        Aangevuld
    </span>
@else
    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-amber-600/15 ring-inset dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-400/20">
        <span class="size-1.5 rounded-full bg-amber-500"></span>
        Wacht op gegevens
    </span>
@endif
