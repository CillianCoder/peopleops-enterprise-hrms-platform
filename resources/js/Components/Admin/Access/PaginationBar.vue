<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import type { PaginationLink, PaginationMeta } from '@/types/access';

defineProps<{
    links: PaginationLink[];
    meta?: PaginationMeta;
}>();

function labelFor(label: string): string {
    return label.replace('&laquo;', '').replace('&raquo;', '').trim();
}
</script>

<template>
    <div class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
        <p v-if="meta" class="text-sm text-slate-500 dark:text-slate-400">
            Showing {{ meta.from ?? 0 }}-{{ meta.to ?? 0 }} of {{ meta.total }}
        </p>
        <div class="flex flex-wrap gap-1.5">
            <Link
                v-for="link in links"
                :key="link.label"
                :href="link.url ?? '#'"
                preserve-scroll
                preserve-state
                class="focus-ring inline-flex h-9 min-w-9 items-center justify-center rounded-md border px-3 text-sm font-medium transition"
                :class="
                    link.active
                        ? 'border-purple-700 bg-purple-50 text-purple-800 dark:border-purple-400 dark:bg-purple-950 dark:text-purple-200'
                        : link.url
                          ? 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800'
                          : 'pointer-events-none border-slate-100 bg-slate-50 text-slate-300 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-600'
                "
            >
                <ChevronLeft v-if="link.label.includes('laquo')" class="size-4" />
                <ChevronRight v-else-if="link.label.includes('raquo')" class="size-4" />
                <span v-else>{{ labelFor(link.label) }}</span>
            </Link>
        </div>
    </div>
</template>
