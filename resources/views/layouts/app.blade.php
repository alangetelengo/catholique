<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="color-scheme" content="light dark">

        <title>@yield('title', config('app.name', 'Catholique'))</title>

        @include('partials.favicon')
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @stack('head-scripts')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="font-sans antialiased min-h-screen bg-slate-100 dark:bg-slate-950">
        <div id="main-wrapper" style="display: flex; flex-direction: column; min-height: 100vh;">
        @include('partials.preload')
        @include('partials.nav-header')
        @include('partials.header')
        @include('partials.sidebar')

        <div id="mainContent" class="main-content flex-1 flex flex-col transition-all duration-300 adventiste-content-canvas" style="margin-top: 80px;">
            @isset($header)
                <header class="adventiste-page-header-shell relative">
                    <div class="relative max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @else
                @hasSection('page-title')
                <header class="@yield('page-header-class', 'adventiste-page-header-shell relative')">
                    <div class="relative @yield('content-container-class', 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8') py-6 sm:py-8 flex flex-wrap items-start sm:items-center justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <h1 class="@yield('page-title-class', 'text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white')">@yield('page-title')</h1>
                            @hasSection('page-title-info')<div class="@yield('page-title-info-class', 'mt-2 text-sm sm:text-base text-slate-600 dark:text-slate-400 leading-relaxed max-w-3xl')">@yield('page-title-info')</div>@endif
                        </div>
                        <div class="flex w-full min-w-0 shrink-0 flex-wrap items-center justify-end gap-2 sm:w-auto">
                            @unless(request()->routeIs('home'))
                                @hasSection('header-back')
                                    @yield('header-back')
                                @else
                                    <x-back-link :href="route('home')" />
                                @endif
                            @endunless
                            @yield('btn-create')
                        </div>
                    </div>
                </header>
                @endif
            @endisset

            <main class="@yield('main-class', 'py-6 sm:py-8')">
                <div class="@yield('content-container-class', 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8')">
                    @include('partials.flash-messages')
                    @hasSection('page-aide')
                        @yield('page-aide')
                    @endif
                    @hasSection('content')
                        @yield('content')
                    @endif
                </div>
            </main>
        </div>

        @include('partials.footer')
        </div>

        @stack('body-modals')
        @include('partials.flash-alert-modal')
        @stack('scripts')
        <style>
            .form-submit-spinner {
                display: inline-block;
                width: 1em;
                height: 1em;
                border: 2px solid currentColor;
                border-right-color: transparent;
                border-radius: 9999px;
                animation: form-submit-spin 0.6s linear infinite;
                vertical-align: -0.2em;
                margin-right: 0.35rem;
            }

            @keyframes form-submit-spin {
                to {
                    transform: rotate(360deg);
                }
            }

            .form-submit-loading {
                opacity: 0.75;
                cursor: not-allowed;
            }
        </style>
        <script>
            (function () {
                document.addEventListener('submit', function (event) {
                    if (event.defaultPrevented) return;

                    var form = event.target;
                    if (!(form instanceof HTMLFormElement)) return;
                    if (form.dataset.skipSubmitLoading === '1') return;

                    var submitter = event.submitter;
                    var btn = submitter instanceof HTMLButtonElement || submitter instanceof HTMLInputElement
                        ? submitter
                        : form.querySelector('button[type="submit"]:not([disabled]), input[type="submit"]:not([disabled])');
                    if (!btn) return;
                    if (btn.dataset.loading === '1') return;

                    btn.dataset.loading = '1';
                    if (!btn.dataset.originalHtml && btn instanceof HTMLButtonElement) {
                        btn.dataset.originalHtml = btn.innerHTML;
                    }

                    var loadingText = btn.dataset.loadingText || form.dataset.loadingText || 'Chargement...';
                    if (btn instanceof HTMLButtonElement) {
                        btn.innerHTML = '<span class="form-submit-spinner"></span> ' + loadingText;
                    } else {
                        btn.value = loadingText;
                    }

                    btn.disabled = true;
                    btn.setAttribute('aria-busy', 'true');
                    btn.classList.add('form-submit-loading');
                });
            })();
        </script>
    </body>
</html>
