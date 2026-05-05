<div>
    <h2 class="text-xl font-semibold mb-4">{{ __('app.goals_title') }}</h2>

    {{-- Create Goal Form --}}
    <form wire:submit="create" class="bg-surface-raised p-4 rounded-2xl mb-6 space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <select
                wire:model="vectorId"
                class="w-full bg-surface-overlay text-text-primary py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
            >
                <option value="">{{ __('app.goals_any_vector') }}</option>
                @foreach($vectors as $vector)
                    <option value="{{ $vector->id }}">{{ $vector->name }}</option>
                @endforeach
            </select>

            <select
                wire:model="period"
                class="w-full bg-surface-overlay text-text-primary py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
            >
                <option value="daily">{{ __('app.goals_daily') }}</option>
                <option value="weekly">{{ __('app.goals_weekly') }}</option>
                <option value="monthly">{{ __('app.goals_monthly') }}</option>
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <select
                wire:model="operator"
                class="w-full bg-surface-overlay text-text-primary py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
            >
                <option value="gte">{{ __('app.goals_gte') }}</option>
                <option value="lte">{{ __('app.goals_lte') }}</option>
            </select>

            <input
                type="number"
                wire:model="targetHours"
                step="0.25"
                min="0.01"
                placeholder="{{ __('app.goals_target_placeholder') }}"
                class="w-full bg-surface-overlay text-text-primary py-3 px-4 rounded-lg placeholder-text-secondary focus:outline-none focus:ring-2 focus:ring-accent"
            >
        </div>

        {{-- Expandable webhook URL --}}
        <div x-data="{ showWebhook: false }">
            <button
                type="button"
                @click="showWebhook = !showWebhook"
                class="text-text-secondary hover:text-text-primary text-sm flex items-center gap-1"
            >
                <svg class="w-4 h-4 transition-transform" :class="showWebhook && 'rotate-90'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                {{ __('app.goals_webhook_toggle') }}
            </button>
            <div x-show="showWebhook" x-collapse class="mt-2">
                <input
                    type="url"
                    wire:model="webhookUrl"
                    placeholder="{{ __('app.goals_webhook_placeholder') }}"
                    class="w-full bg-surface-overlay text-text-primary py-3 px-4 rounded-lg placeholder-text-secondary focus:outline-none focus:ring-2 focus:ring-accent"
                >
                <p class="text-text-secondary text-xs mt-1">{{ __('app.goals_webhook_help') }}</p>
            </div>
        </div>

        @error('targetHours')
            <p class="text-danger text-sm">{{ $message }}</p>
        @enderror
        @error('webhookUrl')
            <p class="text-danger text-sm">{{ $message }}</p>
        @enderror

        <button
            type="submit"
            class="w-full bg-accent hover:bg-accent-hover text-surface font-semibold py-3 px-4 rounded-xl [touch-action:manipulation]"
        >
            {{ __('app.goals_create') }}
        </button>
    </form>

    {{-- Goals List --}}
    @forelse($goalsWithProgress as $item)
        @php $goal = $item['goal']; @endphp

        @if($editingId === $goal->id)
            {{-- Inline Edit Form --}}
            <div class="bg-surface-overlay border-l-4 border-accent p-3 sm:p-4 mb-2 rounded-xl space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <select
                        wire:model="editVectorId"
                        class="w-full bg-surface text-text-primary py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option value="">{{ __('app.goals_any_vector') }}</option>
                        @foreach($vectors as $vector)
                            <option value="{{ $vector->id }}">{{ $vector->name }}</option>
                        @endforeach
                    </select>

                    <select
                        wire:model="editPeriod"
                        class="w-full bg-surface text-text-primary py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option value="daily">{{ __('app.goals_daily') }}</option>
                        <option value="weekly">{{ __('app.goals_weekly') }}</option>
                        <option value="monthly">{{ __('app.goals_monthly') }}</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <select
                        wire:model="editOperator"
                        class="w-full bg-surface text-text-primary py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option value="gte">{{ __('app.goals_gte') }}</option>
                        <option value="lte">{{ __('app.goals_lte') }}</option>
                    </select>

                    <input
                        type="number"
                        wire:model="editTargetHours"
                        step="0.25"
                        min="0.01"
                        class="w-full bg-surface text-text-primary py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                </div>

                <div x-data="{ showWebhook: !!$wire.editWebhookUrl }">
                    <button
                        type="button"
                        @click="showWebhook = !showWebhook"
                        class="text-text-secondary hover:text-text-primary text-sm flex items-center gap-1"
                    >
                        <svg class="w-4 h-4 transition-transform" :class="showWebhook && 'rotate-90'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                        {{ __('app.goals_webhook_toggle') }}
                    </button>
                    <div x-show="showWebhook" x-collapse class="mt-2">
                        <input
                            type="url"
                            wire:model="editWebhookUrl"
                            placeholder="{{ __('app.goals_webhook_placeholder') }}"
                            class="w-full bg-surface text-text-primary py-3 px-4 rounded-lg placeholder-text-secondary focus:outline-none focus:ring-2 focus:ring-accent"
                        >
                    </div>
                </div>

                <div class="flex gap-3">
                    <button
                        wire:click="save"
                        class="flex-1 bg-accent hover:bg-accent-hover text-surface font-semibold py-3 px-4 rounded-xl [touch-action:manipulation]"
                    >
                        {{ __('app.save') }}
                    </button>
                    <button
                        wire:click="cancelEdit"
                        class="flex-1 text-text-secondary hover:text-text-primary py-3 px-4 rounded-xl border border-surface-overlay [touch-action:manipulation]"
                    >
                        {{ __('app.entry_cancel') }}
                    </button>
                </div>
            </div>
        @else
            {{-- Goal Display --}}
            <div class="bg-surface-raised p-3 sm:p-4 mb-2 rounded-xl {{ $item['achieved'] ? 'border border-success/30' : '' }}">
                <div class="flex items-center justify-between gap-3">
                    <div
                        class="flex-1 min-w-0 cursor-pointer"
                        wire:click="startEdit({{ $goal->id }})"
                    >
                        <div class="flex items-center gap-2">
                            @if($item['achieved'])
                                <svg class="w-5 h-5 text-success shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118L1.98 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69l1.519-4.674Z"/></svg>
                            @endif
                            <span class="text-lg font-medium">
                                @if($goal->vector)
                                    <span style="color: {{ $goal->vector->color }}">{{ $goal->vector->name }}</span>
                                @else
                                    {{ __('app.goals_any_vector') }}
                                @endif
                            </span>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 mt-1">
                            <span class="text-text-secondary text-sm">
                                {{ __('app.goals_' . $goal->operator) }}
                                {{ $goal->target_hours }}h
                                / {{ __('app.goals_' . $goal->period) }}
                            </span>
                            <span class="text-sm {{ $item['achieved'] ? 'text-success' : 'text-accent' }}">
                                {{ $item['current_hours'] }}h / {{ $goal->target_hours }}h
                            </span>
                        </div>

                        {{-- Progress bar --}}
                        <div class="mt-2 h-1.5 bg-surface-overlay rounded-full overflow-hidden">
                            <div
                                class="h-full rounded-full transition-all duration-500 {{ $item['achieved'] ? 'bg-success' : 'bg-accent' }}"
                                style="width: {{ min(100, $item['percentage']) }}%"
                            ></div>
                        </div>

                        @if($item['achieved'])
                            <p class="text-success text-xs mt-1 font-medium">{{ __('app.goals_achieved') }}</p>
                        @endif

                        @if($goal->webhook_url)
                            <p class="text-text-secondary text-xs mt-1 truncate">{{ __('app.goals_webhook_active') }}</p>
                        @endif
                    </div>

                    <button
                        wire:click="delete({{ $goal->id }})"
                        wire:confirm="{{ __('app.goals_delete_confirm') }}"
                        class="text-danger hover:text-danger/80 p-3 shrink-0 [touch-action:manipulation]"
                        aria-label="{{ __('app.delete') }}"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                    </button>
                </div>
            </div>
        @endif
    @empty
        <p class="text-text-secondary text-center py-8">{{ __('app.goals_empty') }}</p>
    @endforelse
</div>
