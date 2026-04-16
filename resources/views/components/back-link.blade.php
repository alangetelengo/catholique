{{-- history: true = history.back() avec repli sur href ; false = lien direct --}}
@props([
    'href',
    'label' => 'Retour',
    'history' => true,
])
<a
    href="{{ $href }}"
    data-fallback-url="{{ e($href) }}"
    {{ $attributes->merge(['class' => 'adventiste-btn-secondary text-sm no-underline inline-flex items-center']) }}
    @if ($history)
        onclick="event.preventDefault(); if (window.history.length > 1) { window.history.back(); } else { window.location.href = this.dataset.fallbackUrl; }"
    @endif
>
    <i class="fas fa-arrow-left me-1.5" aria-hidden="true"></i> {{ $label }}
</a>
