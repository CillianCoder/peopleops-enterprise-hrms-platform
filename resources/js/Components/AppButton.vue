<script setup lang="ts">
import { LoaderCircle } from '@lucide/vue';

withDefaults(
    defineProps<{
        type?: 'button' | 'submit';
        variant?: 'primary' | 'secondary' | 'ghost' | 'danger';
        loading?: boolean;
        disabled?: boolean;
    }>(),
    {
        type: 'button',
        variant: 'primary',
        loading: false,
        disabled: false,
    },
);
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        class="focus-ring inline-flex h-10 items-center justify-center gap-2 rounded-md px-4 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-60"
        :class="{
            'bg-purple-700 text-white shadow-sm hover:bg-purple-800': variant === 'primary',
            'border border-slate-200 bg-white text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800':
                variant === 'secondary',
            'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800': variant === 'ghost',
            'bg-red-600 text-white hover:bg-red-700': variant === 'danger',
        }"
    >
        <LoaderCircle v-if="loading" class="size-4 animate-spin" aria-hidden="true" />
        <slot />
    </button>
</template>
