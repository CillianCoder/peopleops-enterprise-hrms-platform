<script setup lang="ts">
const model = defineModel<string | number | null>();

withDefaults(
    defineProps<{
        id: string;
        type?: string;
        autocomplete?: string;
        required?: boolean;
        placeholder?: string;
    }>(),
    {
        type: 'text',
        required: false,
    },
);
</script>

<template>
    <input
        :id="id"
        v-model="model"
        :type="type"
        :autocomplete="autocomplete"
        :required="required"
        :placeholder="placeholder"
        class="focus-ring h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-950 shadow-sm transition placeholder:text-slate-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-50 dark:placeholder:text-slate-500"
    />
</template>
