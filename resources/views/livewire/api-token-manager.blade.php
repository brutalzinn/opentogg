<div>
    <h2 class="text-lg sm:text-xl font-semibold mb-4">{{ __('app.api_tokens_title') }}</h2>

    {{-- Create token --}}
    <form wire:submit="create" class="bg-surface-raised p-3 sm:p-4 mb-4 rounded-xl flex items-end gap-3">
        <div class="flex-1">
            <label class="block text-text-secondary text-sm mb-1">{{ __('app.api_token_name') }}</label>
            <input
                type="text"
                wire:model="tokenName"
                placeholder="{{ __('app.api_token_name_placeholder') }}"
                class="w-full bg-surface-overlay text-text-primary py-3 px-4 rounded-lg placeholder-text-secondary focus:outline-none focus:ring-2 focus:ring-accent"
            >
        </div>
        <button type="submit" class="bg-accent hover:bg-accent-hover text-surface font-semibold py-3 px-5 rounded-xl [touch-action:manipulation]">
            {{ __('app.api_token_create') }}
        </button>
    </form>

    {{-- Newly created token display --}}
    @if($newToken)
        <div class="bg-surface-raised p-3 sm:p-4 mb-4 rounded-xl">
            <p class="text-success text-sm mb-2">{{ __('app.api_token_created_message') }}</p>
            <code class="block bg-surface-overlay text-text-primary py-3 px-4 rounded-lg text-sm break-all select-all mb-3">{{ $newToken }}</code>
            <button wire:click="dismissToken" class="w-full sm:w-auto text-text-secondary hover:text-text-primary py-2.5 px-4 rounded-lg border border-surface-overlay [touch-action:manipulation]">
                {{ __('app.api_token_dismiss') }}
            </button>
        </div>
    @endif

    {{-- Existing tokens --}}
    @forelse($tokens as $token)
        <div class="bg-surface-raised p-3 sm:p-4 mb-2 rounded-xl flex items-center justify-between gap-3">
            <div class="min-w-0">
                <span class="text-base sm:text-lg block truncate">{{ $token->name }}</span>
                <div class="text-text-secondary text-xs sm:text-sm mt-0.5">
                    <span>{{ __('app.api_token_created') }} {{ $token->created_at->diffForHumans() }}</span>
                    @if($token->last_used_at)
                        <span class="ml-2">{{ __('app.api_token_last_used') }} {{ $token->last_used_at->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
            <button
                wire:click="revoke({{ $token->id }})"
                wire:confirm="{{ __('app.api_token_revoke_confirm') }}"
                class="text-danger hover:text-danger/80 p-2.5 shrink-0 [touch-action:manipulation]"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
            </button>
        </div>
    @empty
        <p class="text-text-secondary text-center py-8">{{ __('app.api_tokens_empty') }}</p>
    @endforelse

    <div class="bg-surface-raised p-3 sm:p-4 mt-4 rounded-xl text-center">
        <a href="/docs/api" target="_blank" rel="noopener noreferrer" class="text-accent hover:text-accent-hover [touch-action:manipulation]">{{ __('app.api_docs_link') }}</a>
    </div>
</div>
