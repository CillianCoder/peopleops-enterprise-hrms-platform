<script setup lang="ts">
import { Moon, Sun } from '@lucide/vue';
import { useTheme } from '@/composables/useTheme';

const { isDark, label, toggle } = useTheme();
</script>

<template>
    <button
        type="button"
        class="focus-ring inline-flex size-10 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
        :aria-label="label"
        :title="label"
        @click="toggle"
    >
        <Sun v-if="isDark" class="size-4" aria-hidden="true" />
        <Moon v-else class="size-4" aria-hidden="true" />
    </button>
</template>
