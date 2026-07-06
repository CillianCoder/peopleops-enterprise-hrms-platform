<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
    size?: 'sm' | 'md';
}>(), {
    size: 'md',
});

const rootClasses = computed(() => [
    props.size === 'sm' ? 'size-9 rounded-md' : 'size-10 rounded-lg',
    'relative grid place-items-center overflow-hidden border border-purple-500/20 bg-purple-700 shadow-sm shadow-purple-200/60 dark:border-purple-300/20 dark:shadow-purple-950/40',
]);
</script>

<template>
    <div
        :class="rootClasses"
        aria-hidden="true"
    >
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_28%_20%,rgba(255,255,255,0.28),transparent_32%),linear-gradient(135deg,rgba(255,255,255,0.12),transparent_42%)]" />
        <div class="relative size-6">
            <span class="absolute left-1/2 top-1/2 h-px w-5 -translate-x-1/2 -translate-y-1/2 rotate-45 rounded-full bg-white/55" />
            <span class="absolute left-1/2 top-1/2 h-px w-5 -translate-x-1/2 -translate-y-1/2 -rotate-45 rounded-full bg-white/55" />
            <span class="absolute left-1 top-1 size-2 rounded-full bg-white shadow-sm" />
            <span class="absolute right-1 top-1 size-2 rounded-full bg-purple-100 shadow-sm" />
            <span class="absolute bottom-1 left-1 size-2 rounded-full bg-purple-100 shadow-sm" />
            <span class="absolute bottom-1 right-1 size-2 rounded-full bg-white shadow-sm" />
            <span class="absolute left-1/2 top-1/2 size-2.5 -translate-x-1/2 -translate-y-1/2 rounded-md bg-white shadow-sm" />
        </div>
    </div>
</template>
