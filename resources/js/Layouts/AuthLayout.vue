<script setup lang="ts">
import BrandMark from '@/Components/BrandMark.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';

defineProps<{
    title: string;
    description: string;
}>();
</script>

<template>
    <main
        class="grid min-h-screen bg-white text-slate-950 transition-colors dark:bg-slate-950 dark:text-slate-50 lg:grid-cols-[0.92fr_1.08fr]"
    >
        <section class="relative flex items-center justify-center px-6 py-10">
            <div class="absolute right-6 top-6">
                <ThemeToggle />
            </div>
            <div class="w-full max-w-md">
                <div class="mb-8 flex items-center gap-3">
                    <BrandMark />
                    <div>
                        <p class="text-sm font-semibold text-slate-950 dark:text-slate-50">PeopleOps</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Enterprise HRMS</p>
                    </div>
                </div>

                <div class="mb-8">
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-950 dark:text-slate-50">{{ title }}</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ description }}</p>
                </div>

                <slot />
            </div>
        </section>

        <aside
            class="hidden border-l border-purple-100 bg-gradient-to-br from-purple-50 via-white to-slate-100 p-8 text-slate-950 dark:border-slate-800 dark:from-slate-950 dark:via-slate-950 dark:to-purple-950/40 lg:block"
        >
            <div
                class="flex h-full flex-col justify-between rounded-lg border border-white/80 bg-white/70 p-8 shadow-2xl shadow-purple-200/40 backdrop-blur dark:border-white/10 dark:bg-white/[0.04] dark:shadow-purple-950/20"
            >
                <div>
                    <p class="text-sm font-medium text-purple-700 dark:text-purple-300">
                        Secure workforce operating system
                    </p>
                    <h2 class="mt-6 max-w-lg text-4xl font-semibold leading-tight text-slate-950 dark:text-white">
                        Start clean: verified access, one accountable administrator, and company data from day one.
                    </h2>
                </div>
                <div class="grid grid-cols-3 gap-3 text-sm text-slate-600 dark:text-slate-300">
                    <div
                        class="rounded-md border border-purple-100 bg-white/80 p-4 dark:border-white/10 dark:bg-white/[0.03]"
                    >
                        <p class="text-2xl font-semibold text-slate-950 dark:text-white">RBAC</p>
                        <p class="mt-1">Policy-backed access</p>
                    </div>
                    <div
                        class="rounded-md border border-purple-100 bg-white/80 p-4 dark:border-white/10 dark:bg-white/[0.03]"
                    >
                        <p class="text-2xl font-semibold text-slate-950 dark:text-white">R2</p>
                        <p class="mt-1">Private document storage</p>
                    </div>
                    <div
                        class="rounded-md border border-purple-100 bg-white/80 p-4 dark:border-white/10 dark:bg-white/[0.03]"
                    >
                        <p class="text-2xl font-semibold text-slate-950 dark:text-white">Audit</p>
                        <p class="mt-1">Critical actions logged</p>
                    </div>
                </div>
            </div>
        </aside>
    </main>
</template>
