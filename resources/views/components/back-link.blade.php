@props([
    'href',
    'label' => 'Retour',
])
<a href="{{ $href }}" {{ $attributes->merge(['class' => 'adventiste-btn-secondary text-sm no-underline inline-flex items-center']) }}>
    <i class="fas fa-arrow-left me-1.5" aria-hidden="true"></i> {{ $label }}
</a>
