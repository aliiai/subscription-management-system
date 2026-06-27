<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-l from-[#1aa399] to-[#0a4589] px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-[#0a4589]/20 transition hover:scale-[1.02] hover:shadow-[#1aa399]/30 active:scale-[0.99]']) }}>
    {{ $slot }}
</button>
