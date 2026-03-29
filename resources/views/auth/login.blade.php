<x-layouts.guest>
    <div class="text-center">
        <h1 class="text-3xl font-semibold mb-8 text-accent">{{ config('app.name') }}</h1>
        <a href="/auth/google/redirect"
           class="inline-block bg-accent hover:bg-accent-hover text-surface font-semibold py-3 px-6 text-lg">
            {{ __('app.login_google') }}
        </a>
    </div>
</x-layouts.guest>
