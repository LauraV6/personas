@props(['column', 'sort', 'direction'])

@php($active = $sort === $column)

<th scope="col" {{ $attributes->class('px-4 py-3 font-medium') }} aria-sort="{{ $active ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}">
    <button wire:click="sortBy('{{ $column }}')" type="button" @class([
        'group inline-flex items-center gap-1 rounded transition hover:text-zinc-900 dark:hover:text-white',
        'text-zinc-900 dark:text-white' => $active,
    ])>
        {{ $slot }}
        <svg @class(['size-3.5 transition', 'opacity-0 group-hover:opacity-60' => ! $active]) fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
            @if ($active && $direction === 'asc')
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
            @elseif ($active)
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            @else
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
            @endif
        </svg>
    </button>
</th>
