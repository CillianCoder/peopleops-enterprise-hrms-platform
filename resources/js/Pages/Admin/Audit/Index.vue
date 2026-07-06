<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Activity, CalendarDays, ChevronDown, ChevronUp, Filter, History, Search, ShieldCheck, UserRound, X } from '@lucide/vue';
import { computed, reactive, shallowRef } from 'vue';
import AppButton from '@/Components/AppButton.vue';
import PaginationBar from '@/Components/Admin/Access/PaginationBar.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { AuditActivity, AuditPageProps } from '@/types/audit';

const props = defineProps<AuditPageProps>();

const expandedId = shallowRef<number | null>(props.activities.data[0]?.id ?? null);

const filterForm = reactive({
    search: props.filters.search ?? '',
    module: props.filters.module ?? '',
    event: props.filters.event ?? '',
    actor: props.filters.actor ?? '',
    date_from: props.filters.date_from ?? '',
    date_to: props.filters.date_to ?? '',
    per_page: String(props.filters.per_page ?? 12),
});

const summaryCards = computed(() => [
    { label: 'Total events', value: props.summary.total, icon: History },
    { label: 'Today', value: props.summary.today, icon: CalendarDays },
    { label: 'Security events', value: props.summary.security, icon: ShieldCheck },
    { label: 'Write requests', value: props.summary.writes, icon: Activity },
]);

function applyFilters(): void {
    router.get(
        '/admin/audit',
        {
            search: filterForm.search,
            module: filterForm.module,
            event: filterForm.event,
            actor: filterForm.actor,
            date_from: filterForm.date_from,
            date_to: filterForm.date_to,
            per_page: filterForm.per_page,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
}

function resetFilters(): void {
    filterForm.search = '';
    filterForm.module = '';
    filterForm.event = '';
    filterForm.actor = '';
    filterForm.date_from = '';
    filterForm.date_to = '';
    filterForm.per_page = '12';
    applyFilters();
}

function statusClasses(activity: AuditActivity): string {
    if (activity.status === 'failed') {
        return 'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300';
    }

    if (activity.status === 'warning') {
        return 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300';
    }

    return 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300';
}
</script>

<template>
    <AppLayout title="Audit Logs" description="Review security, access, company, user, role, and system write events in plain language.">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-purple-700 dark:text-purple-300">Audit trail</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950 dark:text-slate-50">Activity ledger</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                    Every critical action is recorded with actor, target, time, outcome, and safe request context for investigation.
                </p>
            </div>
        </div>

        <section class="mb-6 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div
                v-for="card in summaryCards"
                :key="card.label"
                class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ card.label }}</p>
                    <component :is="card.icon" class="size-4 text-purple-600 dark:text-purple-300" />
                </div>
                <p class="mt-2 text-2xl font-semibold text-slate-950 dark:text-slate-50">{{ card.value }}</p>
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-200 p-5 dark:border-slate-800">
                <form class="grid gap-3" @submit.prevent="applyFilters">
                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <div class="relative md:col-span-2">
                            <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                            <input
                                v-model="filterForm.search"
                                class="focus-ring h-10 w-full rounded-md border border-slate-200 bg-white pl-9 pr-3 text-sm text-slate-950 transition placeholder:text-slate-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-50"
                                type="search"
                                placeholder="Search action or module"
                            >
                        </div>
                        <select
                            v-model="filterForm.module"
                            class="focus-ring h-10 rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            aria-label="Filter by module"
                        >
                            <option value="">All modules</option>
                            <option v-for="option in moduleOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                        </select>
                        <select
                            v-model="filterForm.event"
                            class="focus-ring h-10 rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            aria-label="Filter by action"
                        >
                            <option value="">All actions</option>
                            <option v-for="option in eventOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                        </select>
                        <div class="relative">
                            <UserRound class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                            <input
                                v-model="filterForm.actor"
                                class="focus-ring h-10 w-full rounded-md border border-slate-200 bg-white pl-9 pr-3 text-sm text-slate-950 transition placeholder:text-slate-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-50"
                                type="search"
                                placeholder="Actor"
                            >
                        </div>
                        <input
                            v-model="filterForm.date_from"
                            class="focus-ring h-10 rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            type="date"
                            aria-label="From date"
                        >
                        <input
                            v-model="filterForm.date_to"
                            class="focus-ring h-10 rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            type="date"
                            aria-label="To date"
                        >
                        <select
                            v-model="filterForm.per_page"
                            class="focus-ring h-10 rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            aria-label="Rows per page"
                        >
                            <option value="5">5 rows</option>
                            <option value="12">12 rows</option>
                            <option value="20">20 rows</option>
                            <option value="30">30 rows</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-2 border-t border-slate-100 pt-3 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-end">
                        <AppButton type="submit" variant="secondary">
                            <Filter class="size-4" />
                            Apply filters
                        </AppButton>
                        <button
                            type="button"
                            class="focus-ring inline-flex h-10 items-center justify-center gap-2 rounded-md px-3 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-slate-100"
                            aria-label="Reset audit filters"
                            @click="resetFilters"
                        >
                            <X class="size-4" />
                            Reset
                        </button>
                    </div>
                </form>
            </div>

            <div v-if="activities.data.length" class="divide-y divide-slate-100 dark:divide-slate-800">
                <article
                    v-for="activity in activities.data"
                    :key="activity.id"
                    class="transition hover:bg-slate-50/70 dark:hover:bg-slate-950/60"
                >
                    <button
                        type="button"
                        class="focus-ring grid w-full gap-4 px-5 py-4 text-left md:grid-cols-[180px_minmax(0,1fr)_220px_160px_40px]"
                        @click="expandedId = expandedId === activity.id ? null : activity.id"
                    >
                        <div>
                            <span
                                class="inline-flex rounded-full border px-2.5 py-1 text-xs font-medium"
                                :class="statusClasses(activity)"
                            >
                                {{ activity.module }}
                            </span>
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ activity.relativeTime }}</p>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-medium text-slate-950 dark:text-slate-50">{{ activity.eventLabel }}</h3>
                            <p class="mt-1 truncate text-sm text-slate-500 dark:text-slate-400">{{ activity.description }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ activity.actor.name }}</p>
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ activity.actor.email ?? activity.actor.type }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ activity.occurredAtDisplay }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ activity.subject.type }}</p>
                        </div>
                        <div class="flex justify-end">
                            <ChevronUp v-if="expandedId === activity.id" class="size-4 text-slate-400" />
                            <ChevronDown v-else class="size-4 text-slate-400" />
                        </div>
                    </button>

                    <Transition name="audit-detail">
                        <div v-if="expandedId === activity.id" class="border-t border-slate-100 bg-slate-50 px-5 py-4 dark:border-slate-800 dark:bg-slate-950/70">
                            <div class="grid gap-4 lg:grid-cols-[260px_minmax(0,1fr)]">
                                <div class="rounded-md border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Subject</p>
                                    <p class="mt-2 font-medium text-slate-950 dark:text-slate-50">{{ activity.subject.label }}</p>
                                    <p v-if="activity.subject.detail" class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ activity.subject.detail }}</p>
                                </div>
                                <div class="rounded-md border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Details</p>
                                    <div v-if="activity.changes.length" class="mt-3 grid gap-2">
                                        <div
                                            v-for="change in activity.changes"
                                            :key="`${activity.id}-${change.field}`"
                                            class="grid gap-2 rounded-md bg-slate-50 p-3 text-sm dark:bg-slate-950 md:grid-cols-[180px_1fr_1fr]"
                                        >
                                            <p class="font-medium text-slate-800 dark:text-slate-100">{{ change.field }}</p>
                                            <p class="text-slate-500 dark:text-slate-400">From: {{ change.from }}</p>
                                            <p class="text-slate-700 dark:text-slate-200">To: {{ change.to }}</p>
                                        </div>
                                    </div>
                                    <div v-else-if="activity.meta.length" class="mt-3 flex flex-wrap gap-2">
                                        <span
                                            v-for="item in activity.meta"
                                            :key="`${activity.id}-${item.label}`"
                                            class="rounded-md bg-slate-50 px-2.5 py-1.5 text-xs text-slate-600 dark:bg-slate-950 dark:text-slate-300"
                                        >
                                            <span class="font-medium">{{ item.label }}:</span> {{ item.value }}
                                        </span>
                                    </div>
                                    <p v-else class="mt-3 text-sm text-slate-500 dark:text-slate-400">No additional details were recorded for this event.</p>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </article>
            </div>

            <div v-else class="px-6 py-16 text-center">
                <p class="font-medium text-slate-950 dark:text-slate-50">No audit events found</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Try clearing filters or widening the date range.</p>
            </div>

            <PaginationBar :links="activities.links" :meta="activities.meta" />
        </section>
    </AppLayout>
</template>

<style scoped>
.audit-detail-enter-active,
.audit-detail-leave-active {
    transition:
        opacity 160ms ease,
        transform 160ms ease;
}

.audit-detail-enter-from,
.audit-detail-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
</style>
