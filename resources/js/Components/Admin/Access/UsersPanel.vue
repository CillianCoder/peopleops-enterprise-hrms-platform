<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Filter, Pencil, Search, Trash2, UserPlus, X } from '@lucide/vue';
import { computed, reactive, shallowRef } from 'vue';
import AppButton from '@/Components/AppButton.vue';
import FormField from '@/Components/FormField.vue';
import TextInput from '@/Components/TextInput.vue';
import PaginationBar from '@/Components/Admin/Access/PaginationBar.vue';
import StatusBadge from '@/Components/Admin/Access/StatusBadge.vue';
import type { AccessFilters, ManagedUser, PaginatedResponse } from '@/types/access';
import type { SelectOption } from '@/types';

const props = defineProps<{
    users: PaginatedResponse<ManagedUser>;
    filters: AccessFilters;
    assignableRoles: SelectOption[];
    statusOptions: SelectOption[];
}>();

const emit = defineEmits<{
    created: [];
}>();

const editingUser = shallowRef<ManagedUser | null>(null);
const deleteTarget = shallowRef<ManagedUser | null>(null);

const tableFilters = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
    role: props.filters.role ?? '',
    per_page: String(props.filters.per_page ?? 10),
});

const createForm = useForm({
    name: '',
    email: '',
    nic: '',
    job_title: '',
    phone: '',
    role: props.assignableRoles[0]?.value ?? '',
});

const updateForm = useForm({
    name: '',
    email: '',
    nic: '',
    job_title: '',
    phone: '',
    status: 'active',
    role: props.assignableRoles[0]?.value ?? '',
});

const roleFilterOptions = computed<SelectOption[]>(() => [{ value: '', label: 'All roles' }, ...props.assignableRoles]);

function applyFilters(): void {
    router.get(
        '/admin/users',
        {
            search: tableFilters.search,
            status: tableFilters.status,
            role: tableFilters.role,
            per_page: tableFilters.per_page,
            tab: 'users',
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function resetFilters(): void {
    tableFilters.search = '';
    tableFilters.status = '';
    tableFilters.role = '';
    tableFilters.per_page = '10';
    applyFilters();
}

function createUser(): void {
    createForm.post('/admin/users?tab=users', {
        preserveScroll: true,
        onSuccess: () => {
            createForm.reset();
            emit('created');
        },
    });
}

function startEdit(user: ManagedUser): void {
    editingUser.value = user;
    updateForm.defaults({
        name: user.name,
        email: user.email,
        nic: user.nic ?? '',
        job_title: user.jobTitle ?? '',
        phone: user.phone ?? '',
        status: user.status,
        role: user.role ?? props.assignableRoles[0]?.value ?? '',
    });
    updateForm.reset();
    updateForm.clearErrors();
}

function updateUser(): void {
    if (!editingUser.value) {
        return;
    }

    updateForm.put(`/admin/users/${editingUser.value.id}?tab=users`, {
        preserveScroll: true,
        onSuccess: () => {
            editingUser.value = null;
        },
    });
}

function deleteUser(): void {
    if (!deleteTarget.value) {
        return;
    }

    router.delete(`/admin/users/${deleteTarget.value.id}?tab=users`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteTarget.value = null;
        },
    });
}
</script>

<template>
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_390px]">
        <section
            class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <div class="border-b border-slate-200 p-5 dark:border-slate-800">
                <div class="space-y-4">
                    <div class="max-w-xl">
                        <h2 class="text-base font-semibold text-slate-950 dark:text-slate-50">User directory</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Manage login access, user identity, status, and assigned role.
                        </p>
                    </div>
                    <form
                        class="grid gap-2 lg:grid-cols-[minmax(260px,1fr)_150px_150px_100px_auto]"
                        @submit.prevent="applyFilters"
                    >
                        <div class="relative">
                            <Search
                                class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                            />
                            <input
                                v-model="tableFilters.search"
                                class="focus-ring h-10 w-full rounded-md border border-slate-200 bg-white pl-9 pr-3 text-sm text-slate-950 transition placeholder:text-slate-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-50"
                                type="search"
                                placeholder="Name, email, NIC"
                            />
                        </div>
                        <select
                            v-model="tableFilters.status"
                            class="focus-ring h-10 rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            aria-label="Filter by status"
                        >
                            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <select
                            v-model="tableFilters.role"
                            class="focus-ring h-10 rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            aria-label="Filter by role"
                        >
                            <option v-for="option in roleFilterOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <select
                            v-model="tableFilters.per_page"
                            class="focus-ring h-10 rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            aria-label="Rows per page"
                        >
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                        </select>
                        <div class="flex gap-2">
                            <AppButton type="submit" variant="secondary">
                                <Filter class="size-4" />
                                Apply
                            </AppButton>
                            <button
                                type="button"
                                class="focus-ring inline-flex size-10 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-slate-100"
                                aria-label="Reset filters"
                                @click="resetFilters"
                            >
                                <X class="size-4" />
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="users.data.length" class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-left text-sm">
                    <thead
                        class="border-b border-slate-200 bg-slate-50 text-xs font-medium uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400"
                    >
                        <tr>
                            <th class="px-5 py-3">User</th>
                            <th class="px-5 py-3">NIC</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Phone</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            class="transition hover:bg-slate-50/80 dark:hover:bg-slate-950/70"
                        >
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="grid size-10 shrink-0 place-items-center rounded-md bg-slate-100 text-sm font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-200"
                                    >
                                        {{ user.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-950 dark:text-slate-50">{{ user.name }}</p>
                                        <p class="text-slate-500 dark:text-slate-400">{{ user.email }}</p>
                                        <p v-if="user.jobTitle" class="text-xs text-slate-400 dark:text-slate-500">
                                            {{ user.jobTitle }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 font-mono text-xs text-slate-600 dark:text-slate-300">
                                {{ user.nic || 'Not set' }}
                            </td>
                            <td class="px-5 py-4 text-slate-700 dark:text-slate-200">
                                {{ user.roleLabel ?? 'No role' }}
                            </td>
                            <td class="px-5 py-4">
                                <StatusBadge :status="user.isSuperAdmin ? 'protected' : user.status" />
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ user.phone || '-' }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-1.5">
                                    <button
                                        v-if="user.canEdit"
                                        type="button"
                                        class="focus-ring inline-flex size-9 items-center justify-center rounded-md text-slate-500 transition hover:bg-purple-50 hover:text-purple-700 dark:hover:bg-purple-950 dark:hover:text-purple-300"
                                        :aria-label="`Edit ${user.name}`"
                                        @click="startEdit(user)"
                                    >
                                        <Pencil class="size-4" />
                                    </button>
                                    <button
                                        v-if="user.canDelete"
                                        type="button"
                                        class="focus-ring inline-flex size-9 items-center justify-center rounded-md text-slate-500 transition hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-950 dark:hover:text-red-300"
                                        :aria-label="`Delete ${user.name}`"
                                        @click="deleteTarget = user"
                                    >
                                        <Trash2 class="size-4" />
                                    </button>
                                    <span
                                        v-if="!user.canEdit && !user.canDelete"
                                        class="text-xs text-slate-400 dark:text-slate-500"
                                        >Restricted</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="px-6 py-14 text-center">
                <p class="font-medium text-slate-950 dark:text-slate-50">No users found</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Try a lighter search or add a managed user.
                </p>
            </div>

            <PaginationBar :links="users.links" :meta="users.meta" />
        </section>

        <aside class="space-y-6">
            <section
                class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="mb-5 flex items-center gap-3">
                    <div
                        class="grid size-10 place-items-center rounded-md bg-purple-50 text-purple-700 dark:bg-purple-950 dark:text-purple-300"
                    >
                        <UserPlus class="size-5" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-slate-950 dark:text-slate-50">Add user</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Create a login and copy credentials.</p>
                    </div>
                </div>
                <form class="space-y-4" @submit.prevent="createUser">
                    <FormField label="Full name" for-id="create_name" :error="createForm.errors.name">
                        <TextInput id="create_name" v-model="createForm.name" autocomplete="name" required />
                    </FormField>
                    <FormField label="Email" for-id="create_email" :error="createForm.errors.email">
                        <TextInput
                            id="create_email"
                            v-model="createForm.email"
                            type="email"
                            autocomplete="email"
                            required
                        />
                    </FormField>
                    <FormField
                        label="NIC"
                        for-id="create_nic"
                        :error="createForm.errors.nic"
                        helper="Unique identity number for HR records."
                    >
                        <TextInput id="create_nic" v-model="createForm.nic" required />
                    </FormField>
                    <FormField label="Job title" for-id="create_job_title" :error="createForm.errors.job_title">
                        <TextInput id="create_job_title" v-model="createForm.job_title" />
                    </FormField>
                    <FormField label="Phone" for-id="create_phone" :error="createForm.errors.phone">
                        <TextInput id="create_phone" v-model="createForm.phone" type="tel" />
                    </FormField>
                    <FormField label="Role" for-id="create_role" :error="createForm.errors.role">
                        <select
                            id="create_role"
                            v-model="createForm.role"
                            class="focus-ring h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-950 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-50"
                            required
                        >
                            <option disabled value="">Choose role</option>
                            <option v-for="role in assignableRoles" :key="role.value" :value="role.value">
                                {{ role.label }}
                            </option>
                        </select>
                    </FormField>
                    <AppButton type="submit" class="w-full" :loading="createForm.processing">Create login</AppButton>
                </form>
            </section>

            <section
                class="rounded-lg border border-slate-200 bg-white p-5 text-sm leading-6 text-slate-600 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
            >
                Super admin accounts are protected from managed-user edits and deletes. Use roles to grant focused
                access instead of sharing administrator credentials.
            </section>
        </aside>

        <Transition name="drawer-panel">
            <div
                v-if="editingUser"
                class="fixed inset-0 z-40 flex justify-end bg-slate-950/35 p-4 backdrop-blur-sm"
                @click.self="editingUser = null"
            >
                <section
                    class="flex h-full w-full max-w-xl flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-950"
                >
                    <div
                        class="flex items-start justify-between gap-4 border-b border-slate-200 p-5 dark:border-slate-800"
                    >
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-purple-700 dark:text-purple-300">
                                Managed user
                            </p>
                            <h2 class="mt-1 text-lg font-semibold text-slate-950 dark:text-slate-50">Edit user</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ editingUser.email }}</p>
                        </div>
                        <button
                            type="button"
                            class="focus-ring inline-flex size-9 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 dark:hover:bg-slate-900"
                            aria-label="Close edit form"
                            @click="editingUser = null"
                        >
                            <X class="size-4" />
                        </button>
                    </div>
                    <form class="flex min-h-0 flex-1 flex-col" @submit.prevent="updateUser">
                        <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-5">
                            <FormField label="Full name" for-id="edit_name" :error="updateForm.errors.name">
                                <TextInput id="edit_name" v-model="updateForm.name" required />
                            </FormField>
                            <FormField label="Email" for-id="edit_email" :error="updateForm.errors.email">
                                <TextInput id="edit_email" v-model="updateForm.email" type="email" required />
                            </FormField>
                            <FormField label="NIC" for-id="edit_nic" :error="updateForm.errors.nic">
                                <TextInput id="edit_nic" v-model="updateForm.nic" required />
                            </FormField>
                            <FormField label="Job title" for-id="edit_job_title" :error="updateForm.errors.job_title">
                                <TextInput id="edit_job_title" v-model="updateForm.job_title" />
                            </FormField>
                            <FormField label="Phone" for-id="edit_phone" :error="updateForm.errors.phone">
                                <TextInput id="edit_phone" v-model="updateForm.phone" type="tel" />
                            </FormField>
                            <FormField label="Status" for-id="edit_status" :error="updateForm.errors.status">
                                <select
                                    id="edit_status"
                                    v-model="updateForm.status"
                                    class="focus-ring h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-950 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-50"
                                >
                                    <option value="active">Active</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </FormField>
                            <FormField label="Role" for-id="edit_role" :error="updateForm.errors.role">
                                <select
                                    id="edit_role"
                                    v-model="updateForm.role"
                                    class="focus-ring h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-950 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-50"
                                    required
                                >
                                    <option v-for="role in assignableRoles" :key="role.value" :value="role.value">
                                        {{ role.label }}
                                    </option>
                                </select>
                            </FormField>
                        </div>
                        <div
                            class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900/70"
                        >
                            <AppButton type="button" variant="secondary" @click="editingUser = null">Cancel</AppButton>
                            <AppButton type="submit" :loading="updateForm.processing">Save changes</AppButton>
                        </div>
                    </form>
                </section>
            </div>
        </Transition>

        <Transition name="dialog-fade">
            <div
                v-if="deleteTarget"
                class="fixed inset-0 z-40 grid place-items-center bg-slate-950/40 p-4 backdrop-blur-sm"
            >
                <section
                    class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-5 shadow-2xl dark:border-slate-800 dark:bg-slate-950"
                >
                    <h2 class="text-base font-semibold text-slate-950 dark:text-slate-50">Delete user?</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        {{ deleteTarget.name }} will lose access and the account will be removed from active
                        administration lists.
                    </p>
                    <div class="mt-5 flex justify-end gap-2">
                        <AppButton type="button" variant="secondary" @click="deleteTarget = null">Cancel</AppButton>
                        <AppButton type="button" variant="danger" @click="deleteUser">
                            <Trash2 class="size-4" />
                            Delete user
                        </AppButton>
                    </div>
                </section>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.drawer-panel-enter-active,
.drawer-panel-leave-active,
.dialog-fade-enter-active,
.dialog-fade-leave-active {
    transition:
        opacity 180ms ease,
        transform 180ms ease;
}

.drawer-panel-enter-from,
.drawer-panel-leave-to {
    opacity: 0;
    transform: translateX(16px);
}

.dialog-fade-enter-from,
.dialog-fade-leave-to {
    opacity: 0;
    transform: translateY(8px) scale(0.98);
}
</style>
