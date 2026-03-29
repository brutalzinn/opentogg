<div>
    <h2 class="text-xl font-semibold mb-4">{{ __('app.api_tokens_title') }}</h2>

    {{-- Create token --}}
    <form wire:submit="create" class="bg-surface-raised p-4 mb-6 flex items-end gap-4">
        <div class="flex-1">
            <label class="block text-text-secondary text-sm mb-1">{{ __('app.api_token_name') }}</label>
            <input
                type="text"
                wire:model="tokenName"
                placeholder="{{ __('app.api_token_name_placeholder') }}"
                class="w-full bg-surface-overlay text-text-primary text-lg py-3 px-4 placeholder-text-secondary focus:outline-none focus:ring-2 focus:ring-accent"
            >
        </div>
        <button type="submit" class="bg-accent hover:bg-accent-hover text-surface font-semibold py-3 px-6 text-lg">
            {{ __('app.api_token_create') }}
        </button>
    </form>

    {{-- Newly created token display --}}
    @if($newToken)
        <div class="bg-surface-raised p-4 mb-6">
            <p class="text-success text-sm mb-2">{{ __('app.api_token_created_message') }}</p>
            <div class="flex items-center gap-2">
                <code class="flex-1 bg-surface-overlay text-text-primary py-3 px-4 text-sm break-all select-all">{{ $newToken }}</code>
                <button wire:click="dismissToken" class="text-text-secondary hover:text-text-primary py-3 px-6">
                    {{ __('app.api_token_dismiss') }}
                </button>
            </div>
        </div>
    @endif

    {{-- Existing tokens --}}
    @forelse($tokens as $token)
        <div class="bg-surface-raised p-4 mb-2 flex items-center justify-between gap-4">
            <div>
                <span class="text-lg">{{ $token->name }}</span>
                <span class="text-text-secondary text-sm ml-3">{{ __('app.api_token_created') }} {{ $token->created_at->diffForHumans() }}</span>
                @if($token->last_used_at)
                    <span class="text-text-secondary text-sm ml-2">{{ __('app.api_token_last_used') }} {{ $token->last_used_at->diffForHumans() }}</span>
                @endif
            </div>
            <button
                wire:click="revoke({{ $token->id }})"
                wire:confirm="{{ __('app.api_token_revoke_confirm') }}"
                class="text-danger hover:text-danger/80 py-3 px-6"
            >
                {{ __('app.api_token_revoke') }}
            </button>
        </div>
    @empty
        <p class="text-text-secondary text-center py-8">{{ __('app.api_tokens_empty') }}</p>
    @endforelse

    <div class="bg-surface-raised p-4 mt-6 text-center">
        <a href="/api/documentation" target="_blank" class="text-accent hover:text-accent-hover text-lg">{{ __('app.api_docs_link') }}</a>
    </div>
</div>
