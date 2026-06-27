@props(['name'])

@php
    $paths = [
        'mail' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'lock' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
        'building' => 'M3 21h18M5 21V5a2 2 0 012-2h6a2 2 0 012 2v16M9 7h2m-2 4h2m-2 4h2M17 21v-8a1 1 0 011-1h1a1 1 0 011 1v8',
        'user' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21v-1a6 6 0 0112 0v1',
        'phone' => 'M3 5a2 2 0 012-2h2.28a1 1 0 01.95.68l1.2 3.6a1 1 0 01-.5 1.2l-1.6.8a12 12 0 005.92 5.92l.8-1.6a1 1 0 011.2-.5l3.6 1.2a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.4 21 3 14.6 3 6V5z',
    ];
@endphp

<span class="pointer-events-none absolute inset-y-0 start-0 grid w-11 place-items-center text-slate-400">
    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $paths[$name] ?? '' }}" />
    </svg>
</span>
