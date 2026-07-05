<script setup lang="ts">
import { ShieldCheck, UsersRound } from '@lucide/vue';

const activeTab = defineModel<'users' | 'roles'>({ required: true });
</script>

<template>
    <div class="inline-flex rounded-md border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <button
            type="button"
            class="focus-ring inline-flex h-10 items-center gap-2 rounded px-4 text-sm font-medium transition"
            :class="
                activeTab === 'users'
                    ? 'bg-purple-700 text-white shadow-sm'
                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-slate-50'
            "
            @click="activeTab = 'users'"
        >
            <UsersRound class="size-4" />
            Users
        </button>
        <button
            type="button"
            class="focus-ring inline-flex h-10 items-center gap-2 rounded px-4 text-sm font-medium transition"
            :class="
                activeTab === 'roles'
                    ? 'bg-purple-700 text-white shadow-sm'
                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-slate-50'
            "
            @click="activeTab = 'roles'"
        >
            <ShieldCheck class="size-4" />
            Roles & permissions
        </button>
    </div>
</template>
