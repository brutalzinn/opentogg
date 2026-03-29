<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-text-primary min-h-screen">
    <nav class="bg-surface-raised" x-data="{ open: false }">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
            <span class="text-lg font-semibold text-accent">{{ config('app.name') }}</span>

            {{-- Mobile menu button --}}
            <button @click="open = !open" class="md:hidden text-text-secondary py-2 px-3" aria-label="Menu">
                <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            {{-- Desktop nav --}}
            <div class="hidden md:flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="text-text-secondary hover:text-text-primary py-1">{{ __('app.nav_dashboard') }}</a>
                <a href="{{ route('vectors') }}" class="text-text-secondary hover:text-text-primary py-1">{{ __('app.nav_vectors') }}</a>
                <a href="{{ route('tags') }}" class="text-text-secondary hover:text-text-primary py-1">{{ __('app.nav_tags') }}</a>
                <a href="{{ route('reports') }}" class="text-text-secondary hover:text-text-primary py-1">{{ __('app.nav_reports') }}</a>
                <a href="{{ route('settings') }}" class="text-text-secondary hover:text-text-primary py-1">{{ __('app.nav_settings') }}</a>
                <span class="text-text-secondary text-sm">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-text-secondary hover:text-text-primary text-sm py-1 px-2">{{ __('app.logout') }}</button>
                </form>
            </div>
        </div>

        {{-- Mobile nav --}}
        <div x-show="open" @click.away="open = false" class="md:hidden bg-surface-raised px-4 pb-4 space-y-2">
            <a href="{{ route('dashboard') }}" class="block text-text-secondary hover:text-text-primary py-2">{{ __('app.nav_dashboard') }}</a>
            <a href="{{ route('vectors') }}" class="block text-text-secondary hover:text-text-primary py-2">{{ __('app.nav_vectors') }}</a>
            <a href="{{ route('tags') }}" class="block text-text-secondary hover:text-text-primary py-2">{{ __('app.nav_tags') }}</a>
            <a href="{{ route('reports') }}" class="block text-text-secondary hover:text-text-primary py-2">{{ __('app.nav_reports') }}</a>
            <a href="{{ route('settings') }}" class="block text-text-secondary hover:text-text-primary py-2">{{ __('app.nav_settings') }}</a>
            <div class="flex items-center justify-between pt-2">
                <span class="text-text-secondary text-sm">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-text-secondary hover:text-text-primary text-sm py-2 px-3">{{ __('app.logout') }}</button>
                </form>
            </div>
        </div>
    </nav>
    <main class="max-w-4xl mx-auto px-4 py-6 md:py-8">
        {{ $slot }}
    </main>
</body>
</html>
