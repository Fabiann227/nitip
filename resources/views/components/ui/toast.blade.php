<div x-data class="fixed z-[100] top-4 right-4 left-4 sm:left-auto sm:w-96 flex flex-col gap-2 pointer-events-none" aria-live="polite">
    <template x-for="item in $store.toast.items" :key="item.id">
        <div x-show="true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             class="pointer-events-auto flex items-start gap-3 rounded-2xl border bg-white shadow-modal p-4"
             :class="{
                'border-[#bbf7d0]': item.type === 'success',
                'border-[#fecaca]': item.type === 'error',
                'border-warning-border': item.type === 'warning',
                'border-[#bfdbfe]': item.type === 'info',
             }">
            <span class="material-symbols-outlined text-[22px] shrink-0"
                  :class="{ 'text-primary': item.type === 'success', 'text-error': item.type === 'error', 'text-warning': item.type === 'warning', 'text-info': item.type === 'info' }"
                  x-text="{ success: 'check_circle', error: 'error', warning: 'warning', info: 'info' }[item.type] || 'info'"></span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-on-surface" x-show="item.title" x-text="item.title"></p>
                <p class="text-sm text-on-surface-variant" x-text="item.message"></p>
            </div>
            <button type="button" class="text-outline hover:text-on-surface shrink-0" x-on:click="$store.toast.remove(item.id)" aria-label="Tutup">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    </template>
</div>
