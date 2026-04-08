@props([
    'options' => [],
    'placeholder' => 'Pilih...',
    'searchable' => false,
    'size' => 'default',
])

@php
    $triggerHeight = match($size) {
        'sm' => 'h-8',
        default => 'h-9',
    };
@endphp

<div
    x-data="{
        open: false,
        search: '',
        value: @entangle($attributes->wire('model')),
        get selectedLabel() {
            const opt = this.opts.find(o => String(o.value) === String(this.value));
            return opt ? opt.label : null;
        },
        get hasValue() {
            return this.value !== '' && this.value !== null && this.value !== undefined;
        },
        opts: @js(collect($options)->map(fn($label, $value) => ['value' => (string) $value, 'label' => $label])->values()->toArray()),
        get filtered() {
            if (!this.search) return this.opts;
            const s = this.search.toLowerCase();
            return this.opts.filter(o => o.label.toLowerCase().includes(s));
        },
        select(val) {
            this.value = val;
            this.open = false;
            this.search = '';
        },
        reposition() {
            const trigger = this.$refs.trigger;
            const popup = this.$refs.popup;
            if (!trigger || !popup) return;
            const rect = trigger.getBoundingClientRect();
            const popupH = popup.scrollHeight;
            const spaceBelow = window.innerHeight - rect.bottom;
            const goUp = spaceBelow < popupH + 8;
            popup.style.width = rect.width + 'px';
            popup.style.left = rect.left + 'px';
            popup.style.top = (goUp ? Math.max(4, rect.top - popupH - 4) : rect.bottom + 4) + 'px';
            popup.style.transformOrigin = goUp ? 'bottom' : 'top';
        },
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => {
                    this.reposition();
                    if ({{ $searchable ? 'true' : 'false' }}) this.$refs.searchInput?.focus();
                });
            } else {
                this.search = '';
            }
        }
    }"
    @keydown.escape.window="open && (open = false)"
    @resize.window.debounce.100ms="open && reposition()"
    @scroll.window.passive="open && reposition()"
    {{ $attributes->whereDoesntStartWith('wire:model')->except(['options', 'placeholder', 'searchable', 'size', 'class']) }}
    class="{{ $attributes->get('class', '') }}"
>
    <button
        type="button"
        x-ref="trigger"
        @click="toggle()"
        class="flex w-full items-center justify-between gap-1.5 rounded-md border border-input bg-input/30 px-3 text-sm whitespace-nowrap transition-colors outline-none select-none hover:bg-input/50 focus-visible:border-ring focus-visible:ring-1 focus-visible:ring-ring {{ $triggerHeight }}"
        :class="open && 'border-ring ring-3 ring-ring/50'"
    >
        <span
            class="flex flex-1 items-center gap-1.5 text-left truncate"
            :class="!hasValue && 'text-muted-foreground'"
            x-text="selectedLabel ?? '{{ $placeholder }}'"
        ></span>
        <svg class="pointer-events-none size-4 shrink-0 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
    </button>

    {{-- Backdrop --}}
    <div x-show="open" x-cloak @click="open = false; search = ''" class="fixed inset-0 z-[99]"></div>

    {{-- Popup --}}
    <div
        x-ref="popup"
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak
        class="fixed z-[100] min-w-36 overflow-hidden rounded-lg border border-border bg-popover text-popover-foreground shadow-md"
    >
        @if($searchable)
            <div class="p-1.5 pb-0">
                <div class="flex h-8 items-center gap-2 rounded-md bg-muted/50 px-2">
                    <svg class="size-3.5 shrink-0 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input
                        x-ref="searchInput"
                        x-model="search"
                        type="text"
                        placeholder="Cari..."
                        class="h-full w-full border-0 bg-transparent text-sm shadow-none outline-none ring-0 focus:border-0 focus:outline-none focus:ring-0 placeholder:text-muted-foreground"
                        @keydown.stop
                    />
                </div>
            </div>
        @endif
        <div class="no-scrollbar overflow-y-auto overflow-x-hidden p-1.5" style="max-height: 14rem;">
            <template x-for="opt in filtered" :key="opt.value">
                <button
                    type="button"
                    @click="select(opt.value)"
                    class="flex w-full cursor-default items-center justify-between gap-2 rounded-md px-2 py-1.5 text-sm outline-hidden select-none hover:bg-accent hover:text-accent-foreground"
                    :class="String(value) === String(opt.value) && 'bg-accent/50'"
                >
                    <span x-text="opt.label" class="truncate"></span>
                    <svg
                        x-show="String(value) === String(opt.value)"
                        class="size-3.5 shrink-0 text-foreground"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                    ><path d="M20 6 9 17l-5-5"/></svg>
                </button>
            </template>
            <div x-show="filtered.length === 0" class="py-6 text-center text-sm text-muted-foreground">
                Tidak ditemukan
            </div>
        </div>
    </div>
</div>
