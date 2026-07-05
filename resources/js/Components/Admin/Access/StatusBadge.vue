<script setup lang="ts">
import { CheckCircle2, Crown, PauseCircle } from '@lucide/vue';

defineProps<{
    status: 'active' | 'suspended' | 'protected';
}>();
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium capitalize"
        :class="{
            'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300': status === 'active',
            'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-300': status === 'suspended',
            'border-purple-200 bg-purple-50 text-purple-700 dark:border-purple-900 dark:bg-purple-950 dark:text-purple-300': status === 'protected',
        }"
    >
        <CheckCircle2 v-if="status === 'active'" class="size-3.5" />
        <PauseCircle v-else-if="status === 'suspended'" class="size-3.5" />
        <Crown v-else class="size-3.5" />
        {{ status }}
    </span>
</template>
