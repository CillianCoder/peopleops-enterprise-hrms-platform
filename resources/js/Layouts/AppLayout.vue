<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Building2, LayoutDashboard, LogOut, UsersRound } from '@lucide/vue';
import { computed } from 'vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import type { SharedProps } from '@/types';

defineProps<{
    title: string;
    description?: string;
}>();

const page = usePage<SharedProps>();

const user = computed(() => page.props.auth.user);
const company = computed(() => page.props.company);

function logout(): void {
    router.post('/logout');
}
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-950 transition-colors dark:bg-slate-950 dark:text-slate-50">
        <aside class="fixed inset-y-0 left-0 hidden w-72 border-r border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950 lg:block">
            <div class="flex h-16 items-center gap-3 border-b border-slate-200 px-6 dark:border-slate-800">
                <img
                    v-if="company?.logoUrl"
                    :src="company.logoUrl"
                    alt=""
                    class="size-9 rounded-md border border-slate-200 object-contain dark:border-slate-700"
                >
                <div v-else class="grid size-9 place-items-center rounded-md bg-purple-700 text-xs font-bold text-white">PO</div>
                <div>
                    <p class="text-sm font-semibold text-slate-950 dark:text-slate-50">{{ company?.name ?? 'PeopleOps' }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Admin workspace</p>
                </div>
            </div>
            <nav class="space-y-1 px-3 py-4">
                <Link
                    href="/dashboard"
                    class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-900"
                >
                    <LayoutDashboard class="size-4" />
                    Dashboard
                </Link>
                <Link
                    href="/admin/users"
                    class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-900"
                >
                    <UsersRound class="size-4" />
                    Users
                </Link>
                <Link
                    href="/company/onboarding"
                    class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-900"
                >
                    <Building2 class="size-4" />
                    Company Profile
                </Link>
            </nav>
        </aside>

        <div class="lg:pl-72">
            <header class="sticky top-0 z-10 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur dark:border-slate-800 dark:bg-slate-950/90 lg:px-8">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">PeopleOps / {{ title }}</p>
                    <h1 class="text-lg font-semibold text-slate-950 dark:text-slate-50">{{ title }}</h1>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ user?.name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ user?.email }}</p>
                    </div>
                    <ThemeToggle />
                    <button
                        class="focus-ring inline-flex size-10 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                        type="button"
                        aria-label="Log out"
                        @click="logout"
                    >
                        <LogOut class="size-4" />
                    </button>
                </div>
            </header>

            <main class="px-4 py-6 lg:px-8">
                <div class="mb-6">
                    <p v-if="description" class="mt-1 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ description }}</p>
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
