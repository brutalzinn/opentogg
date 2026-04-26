<div>
    <h2 class="text-lg sm:text-xl font-semibold mb-4">{{ __('app.data_title') }}</h2>

    @if(session('success'))
        <div class="bg-success/20 text-success p-3 mb-4 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="bg-danger/20 text-danger p-3 mb-4 rounded-xl text-sm">{{ session('error') }}</div>
    @endif

    {{-- Export --}}
    <div class="bg-surface-raised p-3 sm:p-4 mb-4 rounded-xl">
        <h3 class="text-base sm:text-lg font-semibold mb-2">{{ __('app.data_export_title') }}</h3>
        <p class="text-text-secondary text-sm mb-4">{{ __('app.data_export_description') }}</p>
        <a
            href="{{ route('data.export') }}"
            class="inline-block bg-accent hover:bg-accent-hover text-surface font-semibold py-3 px-6 rounded-xl [touch-action:manipulation]"
        >
            {{ __('app.data_export_button') }}
        </a>
    </div>

    {{-- Import --}}
    <div class="bg-surface-raised p-3 sm:p-4 mb-4 rounded-xl">
        <h3 class="text-base sm:text-lg font-semibold mb-2">{{ __('app.data_import_title') }}</h3>
        <p class="text-text-secondary text-sm mb-4">{{ __('app.data_import_description') }}</p>
        <form action="{{ route('data.import') }}" method="POST" enctype="multipart/form-data" class="space-y-3 sm:space-y-0 sm:flex sm:items-end sm:gap-3">
            @csrf
            <div class="flex-1">
                <input
                    type="file"
                    name="file"
                    accept=".json"
                    class="w-full bg-surface-overlay text-text-primary py-3 px-4 rounded-lg file:bg-surface-overlay file:text-text-primary file:border-0 file:mr-4"
                >
                @error('file')
                    <span class="text-danger text-sm">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="w-full sm:w-auto bg-accent hover:bg-accent-hover text-surface font-semibold py-3 px-6 rounded-xl [touch-action:manipulation]">
                {{ __('app.data_import_button') }}
            </button>
        </form>
    </div>
</div>
