<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { AlertTriangle, ShieldCheck, UserPlus, UsersRound } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps<{
    metrics: {
        activeUsers: number;
        invitedUsers: number;
        suspendedUsers: number;
        rolesConfigured: number;
    };
    recentUsers: Array<{
        id: number;
        name: string;
        email: string;
        status: string;
        role?: string | null;
        createdAt?: string | null;
    }>;
}>();
</script>

<template>
    <AppLayout title="Admin dashboard" description="A secure starting point for company setup, access control, and early HR operations.">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <UsersRound class="mb-4 size-5 text-purple-700" />
                <p class="text-sm text-slate-500 dark:text-slate-400">Active users</p>
                <p class="mt-1 text-3xl font-semibold text-slate-950 dark:text-slate-50">{{ metrics.activeUsers }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <UserPlus class="mb-4 size-5 text-blue-600" />
                <p class="text-sm text-slate-500 dark:text-slate-400">Invited users</p>
                <p class="mt-1 text-3xl font-semibold text-slate-950 dark:text-slate-50">{{ metrics.invitedUsers }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <AlertTriangle class="mb-4 size-5 text-amber-600" />
                <p class="text-sm text-slate-500 dark:text-slate-400">Suspended users</p>
                <p class="mt-1 text-3xl font-semibold text-slate-950 dark:text-slate-50">{{ metrics.suspendedUsers }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <ShieldCheck class="mb-4 size-5 text-emerald-600" />
                <p class="text-sm text-slate-500 dark:text-slate-400">Roles configured</p>
                <p class="mt-1 text-3xl font-semibold text-slate-950 dark:text-slate-50">{{ metrics.rolesConfigured }}</p>
            </div>
        </div>

        <section class="mt-6 rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                <div>
                    <h2 class="text-base font-semibold text-slate-950 dark:text-slate-50">Recent user access</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Newly created or invited accounts in your company.</p>
                </div>
                <Link href="/admin/users" class="rounded-md bg-purple-700 px-4 py-2 text-sm font-medium text-white hover:bg-purple-800">
                    Manage users
                </Link>
            </div>
            <div v-if="recentUsers.length" class="divide-y divide-slate-100 dark:divide-slate-800">
                <div v-for="user in recentUsers" :key="user.id" class="grid gap-3 px-6 py-4 md:grid-cols-[1fr_160px_120px] md:items-center">
                    <div>
                        <p class="font-medium text-slate-950 dark:text-slate-50">{{ user.name }}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ user.email }}</p>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ user.role ?? 'No role' }}</p>
                    <span class="w-fit rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium capitalize text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ user.status }}</span>
                </div>
            </div>
            <div v-else class="px-6 py-12 text-center">
                <p class="font-medium text-slate-950 dark:text-slate-50">No users yet</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Create HR, manager, finance, and employee accounts from user management.</p>
            </div>
        </section>
    </AppLayout>
</template>
