<script setup lang="ts">
import { Link, router, useForm } from '@inertiajs/vue3';
import { Search, UserPlus } from '@lucide/vue';
import { computed, shallowRef } from 'vue';
import AppButton from '@/Components/AppButton.vue';
import FormField from '@/Components/FormField.vue';
import TextInput from '@/Components/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { SelectOption } from '@/types';

interface ManagedUser {
    id: number;
    name: string;
    email: string;
    jobTitle?: string | null;
    phone?: string | null;
    status: string;
    role?: string | null;
    isSuperAdmin: boolean;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps<{
    users: {
        data: ManagedUser[];
        links: PaginationLink[];
    };
    filters: {
        search?: string;
    };
    assignableRoles: SelectOption[];
}>();

const search = shallowRef(props.filters.search ?? '');
const editingUser = shallowRef<ManagedUser | null>(null);

const createForm = useForm({
    name: '',
    email: '',
    job_title: '',
    phone: '',
    role: props.assignableRoles[0]?.value ?? 'employee',
});

const updateForm = useForm({
    name: '',
    email: '',
    job_title: '',
    phone: '',
    status: 'active',
    role: props.assignableRoles[0]?.value ?? 'employee',
});

const editableUsers = computed(() => props.users.data.filter((user) => !user.isSuperAdmin));

function paginationLabel(label: string): string {
    return label.replace('&laquo;', 'Previous').replace('&raquo;', 'Next');
}

function runSearch(): void {
    router.get(
        '/admin/users',
        { search: search.value },
        {
            preserveState: true,
            replace: true,
        },
    );
}

function createUser(): void {
    createForm.post('/admin/users', {
        preserveScroll: true,
        onSuccess: () => createForm.reset(),
    });
}

function startEdit(user: ManagedUser): void {
    editingUser.value = user;
    updateForm.defaults({
        name: user.name,
        email: user.email,
        job_title: user.jobTitle ?? '',
        phone: user.phone ?? '',
        status: user.status,
        role: user.role ?? props.assignableRoles[0]?.value ?? 'employee',
    });
    updateForm.reset();
    updateForm.clearErrors();
}

function updateUser(): void {
    if (!editingUser.value) {
        return;
    }

    updateForm.put(`/admin/users/${editingUser.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingUser.value = null;
        },
    });
}
</script>

<template>
    <AppLayout title="Users" description="Create and manage company users. Administrator signup stays closed after the first system account.">
        <div class="grid gap-6 xl:grid-cols-[1fr_380px]">
            <section class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col gap-3 border-b border-slate-200 px-6 py-4 dark:border-slate-800 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-950 dark:text-slate-50">User directory</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Filter by name, email, or job title.</p>
                    </div>
                    <form class="flex gap-2" @submit.prevent="runSearch">
                        <div class="relative">
                            <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                            <input
                                v-model="search"
                                class="focus-ring h-10 w-full rounded-md border border-slate-200 bg-white pl-9 pr-3 text-sm text-slate-950 placeholder:text-slate-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-50 dark:placeholder:text-slate-500 md:w-72"
                                type="search"
                                placeholder="Search users"
                            />
                        </div>
                        <AppButton type="submit" variant="secondary">Search</AppButton>
                    </form>
                </div>

                <div v-if="users.data.length" class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs font-medium uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
                            <tr>
                                <th class="px-6 py-3">User</th>
                                <th class="px-6 py-3">Role</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Phone</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="user in users.data" :key="user.id" class="dark:hover:bg-slate-950/60">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-slate-950 dark:text-slate-50">{{ user.name }}</p>
                                    <p class="text-slate-500 dark:text-slate-400">{{ user.email }}</p>
                                    <p v-if="user.jobTitle" class="text-xs text-slate-400 dark:text-slate-500">{{ user.jobTitle }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ user.role ?? 'No role' }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium capitalize text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                        {{ user.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ user.phone ?? '-' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button
                                        v-if="!user.isSuperAdmin"
                                        class="text-sm font-medium text-purple-700 hover:text-purple-800 dark:text-purple-300 dark:hover:text-purple-200"
                                        type="button"
                                        @click="startEdit(user)"
                                    >
                                        Edit
                                    </button>
                                    <span v-else class="text-xs text-slate-400 dark:text-slate-500">Protected admin</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="px-6 py-12 text-center">
                    <p class="font-medium text-slate-950 dark:text-slate-50">No matching users</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Adjust the search or create the first managed user.</p>
                </div>

                <div class="flex flex-wrap gap-2 border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                    <Link
                        v-for="link in users.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        class="rounded-md border px-3 py-1.5 text-sm"
                        :class="
                            link.active
                                ? 'border-purple-700 bg-purple-50 text-purple-800 dark:border-purple-400 dark:bg-purple-950 dark:text-purple-200'
                                : 'border-slate-200 text-slate-600 dark:border-slate-700 dark:text-slate-300'
                        "
                    >
                        {{ paginationLabel(link.label) }}
                    </Link>
                </div>
            </section>

            <aside class="space-y-6">
                <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-5 flex items-center gap-3">
                        <UserPlus class="size-5 text-purple-700" />
                        <h2 class="text-base font-semibold text-slate-950 dark:text-slate-50">Add user</h2>
                    </div>
                    <form class="space-y-4" @submit.prevent="createUser">
                        <FormField label="Full name" for-id="create_name" :error="createForm.errors.name">
                            <TextInput id="create_name" v-model="createForm.name" required />
                        </FormField>
                        <FormField label="Email" for-id="create_email" :error="createForm.errors.email">
                            <TextInput id="create_email" v-model="createForm.email" type="email" required />
                        </FormField>
                        <FormField label="Job title" for-id="create_job_title" :error="createForm.errors.job_title">
                            <TextInput id="create_job_title" v-model="createForm.job_title" />
                        </FormField>
                        <FormField label="Role" for-id="create_role" :error="createForm.errors.role">
                            <select
                                id="create_role"
                                v-model="createForm.role"
                                class="focus-ring h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-950 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-50"
                            >
                                <option v-for="role in assignableRoles" :key="role.value" :value="role.value">{{ role.label }}</option>
                            </select>
                        </FormField>
                        <AppButton type="submit" class="w-full" :loading="createForm.processing">Create and invite</AppButton>
                    </form>
                </section>

                <section v-if="editingUser" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="mb-5 text-base font-semibold text-slate-950 dark:text-slate-50">Edit user</h2>
                    <form class="space-y-4" @submit.prevent="updateUser">
                        <FormField label="Full name" for-id="edit_name" :error="updateForm.errors.name">
                            <TextInput id="edit_name" v-model="updateForm.name" required />
                        </FormField>
                        <FormField label="Email" for-id="edit_email" :error="updateForm.errors.email">
                            <TextInput id="edit_email" v-model="updateForm.email" type="email" required />
                        </FormField>
                        <FormField label="Job title" for-id="edit_job_title" :error="updateForm.errors.job_title">
                            <TextInput id="edit_job_title" v-model="updateForm.job_title" />
                        </FormField>
                        <FormField label="Status" for-id="edit_status" :error="updateForm.errors.status">
                            <select
                                id="edit_status"
                                v-model="updateForm.status"
                                class="focus-ring h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-950 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-50"
                            >
                                <option value="active">Active</option>
                                <option value="invited">Invited</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </FormField>
                        <FormField label="Role" for-id="edit_role" :error="updateForm.errors.role">
                            <select
                                id="edit_role"
                                v-model="updateForm.role"
                                class="focus-ring h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-950 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-50"
                            >
                                <option v-for="role in assignableRoles" :key="role.value" :value="role.value">{{ role.label }}</option>
                            </select>
                        </FormField>
                        <div class="flex gap-2">
                            <AppButton type="submit" :loading="updateForm.processing">Save changes</AppButton>
                            <AppButton type="button" variant="secondary" @click="editingUser = null">Cancel</AppButton>
                        </div>
                    </form>
                </section>

                <section
                    v-else-if="editableUsers.length === 0"
                    class="rounded-lg border border-slate-200 bg-white p-6 text-sm text-slate-600 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                >
                    The system administrator account is protected. Create managed users for HR, managers, finance, recruiters, and employees.
                </section>
            </aside>
        </div>
    </AppLayout>
</template>
