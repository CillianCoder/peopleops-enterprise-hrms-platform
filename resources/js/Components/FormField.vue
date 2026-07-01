<script setup lang="ts">
defineProps<{
    label: string;
    error?: string;
    helper?: string;
    forId: string;
}>();
</script>

<template>
    <div class="space-y-1.5">
        <label :for="forId" class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ label }}</label>
        <slot />
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
        <p v-else-if="helper" class="text-sm text-slate-500 dark:text-slate-400">{{ helper }}</p>
    </div>
</template>
