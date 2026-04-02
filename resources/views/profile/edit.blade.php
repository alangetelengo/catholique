@extends('layouts.app')

@section('title', 'Mon profil')
@section('page-title', 'Mon profil')

@section('content')
<div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-linear-to-br from-slate-800 via-slate-900 to-slate-800 text-white shadow-lg overflow-hidden mb-8">
    <div class="px-6 py-8 sm:px-8 flex flex-wrap items-center gap-6">
        <div class="shrink-0 w-20 h-20 rounded-full bg-white/15 ring-2 ring-white/35 flex items-center justify-center text-2xl font-extrabold tracking-wide text-emerald-300">
            @php
                $initials = collect(explode(' ', trim($user->name ?? 'U')))->filter()->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('');
            @endphp
            {{ $initials ?: 'U' }}
        </div>
        <div class="min-w-0 flex-1">
            <h2 class="text-xl sm:text-2xl font-bold text-white m-0 mb-1">{{ $user->name }}</h2>
            <div class="text-sm text-slate-300 flex flex-wrap gap-x-3 gap-y-1">
                <span>{{ $user->email }}</span>
                @if($user->paroisse?->nom)
                    <span class="text-slate-500 hidden sm:inline">·</span>
                    <span>{{ $user->paroisse->nom }}</span>
                @endif
                @php($roles = $user->getRoleNames())
                @if($roles->count())
                    <span class="text-slate-500 hidden sm:inline">·</span>
                    <span>{{ $roles->implode(', ') }}</span>
                @endif
            </div>
            @if($user->username)
                <span class="inline-flex mt-3 rounded-full px-3 py-1 text-xs font-semibold bg-white/10 text-white ring-1 ring-white/20">{{ $user->username }}</span>
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="lg:col-span-8">
        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-600/60 bg-slate-50/80 dark:bg-slate-900/40 flex items-center gap-2">
                <i class="fas fa-user text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                <h3 class="text-base font-semibold text-slate-900 dark:text-white m-0">Informations du compte</h3>
            </div>
            <div class="p-6">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5" for="profile_name">Nom <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" id="profile_name" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 @error('name') border-rose-500 ring-rose-500/30 @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5" for="profile_email">Email <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" id="profile_email" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 @error('email') border-rose-500 ring-rose-500/30 @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5" for="profile_username">Nom d'utilisateur</label>
                            <input type="text" name="username" id="profile_username" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 @error('username') border-rose-500 ring-rose-500/30 @enderror" value="{{ old('username', $user->username) }}" placeholder="Optionnel">
                            @error('username')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-600">
                        <button type="submit" class="adventiste-btn-primary">
                            <i class="fas fa-save mr-1.5" aria-hidden="true"></i>
                            Enregistrer le profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="lg:col-span-4">
        <div class="rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-slate-600/60 bg-slate-50/80 dark:bg-slate-900/40 flex items-center gap-2">
                <i class="fas fa-lock text-amber-600 dark:text-amber-400" aria-hidden="true"></i>
                <h3 class="text-base font-semibold text-slate-900 dark:text-white m-0">Sécurité</h3>
            </div>
            <div class="p-6">
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                    Modifiez votre mot de passe pour renforcer la sécurité de votre compte.
                </p>
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5" for="current_password">Mot de passe actuel <span class="text-rose-500">*</span></label>
                            <input type="password" name="current_password" id="current_password" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 @error('current_password') border-rose-500 @enderror" required placeholder="••••••••">
                            @error('current_password')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5" for="password">Nouveau mot de passe <span class="text-rose-500">*</span></label>
                            <input type="password" name="password" id="password" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35 @error('password') border-rose-500 @enderror" required placeholder="••••••••">
                            @error('password')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5" for="password_confirmation">Confirmer le nouveau <span class="text-rose-500">*</span></label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/35" required placeholder="••••••••">
                        </div>
                    </div>
                    <button type="submit" class="mt-6 w-full inline-flex items-center justify-center gap-2 rounded-xl border border-amber-300 dark:border-amber-600 bg-amber-50 dark:bg-amber-950/40 px-4 py-2.5 text-sm font-semibold text-amber-900 dark:text-amber-100 shadow-sm hover:bg-amber-100 dark:hover:bg-amber-900/50 transition-colors">
                        <i class="fas fa-lock" aria-hidden="true"></i>
                        Mettre à jour le mot de passe
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
