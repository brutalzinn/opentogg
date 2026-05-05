<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-text-primary min-h-screen pb-16 md:pb-0">
    {{-- Top bar: logo + user (desktop: full nav) --}}
    <nav class="bg-surface-raised sticky top-0 z-40">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
            <span class="text-lg font-semibold text-accent">{{ config('app.name') }}</span>

            {{-- Desktop nav --}}
            <div class="hidden md:flex items-center gap-6">
                <a href="{{ route('dashboard') }}" @class(['py-1', 'text-accent' => request()->routeIs('dashboard'), 'text-text-secondary hover:text-text-primary' => !request()->routeIs('dashboard')])>{{ __('app.nav_dashboard') }}</a>
                <a href="{{ route('vectors') }}" @class(['py-1', 'text-accent' => request()->routeIs('vectors'), 'text-text-secondary hover:text-text-primary' => !request()->routeIs('vectors')])>{{ __('app.nav_vectors') }}</a>
                <a href="{{ route('tags') }}" @class(['py-1', 'text-accent' => request()->routeIs('tags'), 'text-text-secondary hover:text-text-primary' => !request()->routeIs('tags')])>{{ __('app.nav_tags') }}</a>
                <a href="{{ route('goals') }}" @class(['py-1', 'text-accent' => request()->routeIs('goals'), 'text-text-secondary hover:text-text-primary' => !request()->routeIs('goals')])>{{ __('app.nav_goals') }}</a>
                <a href="{{ route('reports') }}" @class(['py-1', 'text-accent' => request()->routeIs('reports'), 'text-text-secondary hover:text-text-primary' => !request()->routeIs('reports')])>{{ __('app.nav_reports') }}</a>
                <a href="{{ route('settings') }}" @class(['py-1', 'text-accent' => request()->routeIs('settings'), 'text-text-secondary hover:text-text-primary' => !request()->routeIs('settings')])>{{ __('app.nav_settings') }}</a>
                <span class="text-text-secondary text-sm">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-text-secondary hover:text-text-primary text-sm py-1 px-2">{{ __('app.logout') }}</button>
                </form>
            </div>

            {{-- Mobile: user name + logout --}}
            <div class="flex md:hidden items-center gap-3">
                <span class="text-text-secondary text-sm">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-text-secondary hover:text-text-primary text-sm p-2 [touch-action:manipulation]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    {{-- Toast notifications --}}
    <div
        x-data="{
            toasts: [],
            add(message, type = 'success') {
                const id = Date.now();
                this.toasts.push({ id, message, type });
                setTimeout(() => this.remove(id), 5000);
            },
            remove(id) {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }
        }"
        @goal-achieved.window="add($event.detail.message, 'success')"
        class="fixed top-16 right-4 z-50 space-y-2 max-w-sm"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-[-1rem]"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="bg-surface-raised border border-success/30 text-text-primary px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 cursor-pointer"
                @click="remove(toast.id)"
            >
                <svg class="w-6 h-6 text-success shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118L1.98 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69l1.519-4.674Z"/></svg>
                <span x-text="toast.message" class="text-sm font-medium"></span>
            </div>
        </template>
    </div>

    <main class="max-w-4xl mx-auto px-3 sm:px-4 py-4 md:py-8">
        {{ $slot }}
    </main>

    {{-- Mobile bottom tab bar --}}
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-surface-raised border-t border-surface-overlay safe-area-bottom">
        <div class="flex items-stretch justify-around">
            <a href="{{ route('dashboard') }}" @class(['flex flex-col items-center justify-center py-2 px-1 flex-1 [touch-action:manipulation]', 'text-accent' => request()->routeIs('dashboard'), 'text-text-secondary' => !request()->routeIs('dashboard')])>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span class="text-[10px] mt-0.5">{{ __('app.nav_dashboard') }}</span>
            </a>
            <a href="{{ route('vectors') }}" @class(['flex flex-col items-center justify-center py-2 px-1 flex-1 [touch-action:manipulation]', 'text-accent' => request()->routeIs('vectors'), 'text-text-secondary' => !request()->routeIs('vectors')])>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/></svg>
                <span class="text-[10px] mt-0.5">{{ __('app.nav_vectors') }}</span>
            </a>
            <a href="{{ route('tags') }}" @class(['flex flex-col items-center justify-center py-2 px-1 flex-1 [touch-action:manipulation]', 'text-accent' => request()->routeIs('tags'), 'text-text-secondary' => !request()->routeIs('tags')])>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/></svg>
                <span class="text-[10px] mt-0.5">{{ __('app.nav_tags') }}</span>
            </a>
            <a href="{{ route('goals') }}" @class(['flex flex-col items-center justify-center py-2 px-1 flex-1 [touch-action:manipulation]', 'text-accent' => request()->routeIs('goals'), 'text-text-secondary' => !request()->routeIs('goals')])>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118L1.98 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69l1.519-4.674Z"/></svg>
                <span class="text-[10px] mt-0.5">{{ __('app.nav_goals') }}</span>
            </a>
            <a href="{{ route('reports') }}" @class(['flex flex-col items-center justify-center py-2 px-1 flex-1 [touch-action:manipulation]', 'text-accent' => request()->routeIs('reports'), 'text-text-secondary' => !request()->routeIs('reports')])>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                <span class="text-[10px] mt-0.5">{{ __('app.nav_reports') }}</span>
            </a>
            <a href="{{ route('settings') }}" @class(['flex flex-col items-center justify-center py-2 px-1 flex-1 [touch-action:manipulation]', 'text-accent' => request()->routeIs('settings'), 'text-text-secondary' => !request()->routeIs('settings')])>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                <span class="text-[10px] mt-0.5">{{ __('app.nav_settings') }}</span>
            </a>
        </div>
    </nav>
</body>
</html>
