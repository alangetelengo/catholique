@props([
'variant' => 'edit', // edit, delete, view
'href' => null,
'action' => null,
'method' => 'POST',
'confirmMessage' => null,
'confirmIcon' => '🗑️',
'confirmText' => 'Supprimer',
'title' => null,
'disabled' => false,
'customClasses' => null,
])

@php
$baseClasses = 'inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold shadow-sm transition-all duration-200 focus:outline-none';
$editClasses = $customClasses ?? 'border border-emerald-200 dark:border-emerald-800/80 bg-emerald-50/80 dark:bg-emerald-950/35 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 focus:ring-2 focus:ring-emerald-400/30';
$deleteClasses = 'border border-red-200 dark:border-red-800/80 bg-red-50/80 dark:bg-red-950/35 text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-950/55 focus:ring-2 focus:ring-red-400/30';
$viewClasses = 'border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800/90 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 focus:ring-2 focus:ring-slate-400/30';
@endphp

@if ($variant === 'edit')
<a href="{{ $href }}" class="{{ $baseClasses }} {{ $editClasses }}" title="{{ $title ?? 'Modifier' }}">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
    </svg>
    <span>{{ $title ?? 'Modifier' }}</span>
</a>
@elseif ($variant === 'view')
<a href="{{ $href }}" class="{{ $baseClasses }} {{ $viewClasses }}" title="{{ $title ?? 'Voir' }}">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
    </svg>
    <span>{{ $title ?? 'Voir' }}</span>
</a>
@elseif ($variant === 'delete')
<form method="{{ $method }}" action="{{ $action }}" class="inline m-0">
    @csrf
    @if ($method !== 'POST')
    @method($method)
    @endif
    <button type="button" class="{{ $baseClasses }} {{ $deleteClasses }}" title="{{ $title ?? 'Supprimer' }}" onclick="flashAlert('{{ $confirmMessage }}', this.closest('form'), { icon: '{{ $confirmIcon }}', danger: true, confirmText: '{{ $confirmText }}' })" {{ $disabled ? 'disabled' : '' }}>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        <span>{{ $title ?? 'Supprimer' }}</span>
    </button>
</form>
@endif
