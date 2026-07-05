<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { LockKeyhole, Pencil, Plus, Search, ShieldCheck, Trash2, X } from '@lucide/vue';
import { computed, reactive, shallowRef } from 'vue';
import AppButton from '@/Components/AppButton.vue';
import FormField from '@/Components/FormField.vue';
import TextInput from '@/Components/TextInput.vue';
import PaginationBar from '@/Components/Admin/Access/PaginationBar.vue';
import type { AccessFilters, ManagedRole, PaginatedResponse, PermissionGroup } from '@/types/access';

const props = defineProps<{
    roles: PaginatedResponse<ManagedRole>;
    filters: AccessFilters;
    permissionGroups: PermissionGroup[];
}>();

const editingRole = shallowRef<ManagedRole | null>(null);
const deleteTarget = shallowRef<ManagedRole | null>(null);

const roleFilters = reactive({
    role_search: props.filters.role_search ?? '',
});

const createForm = useForm({
    name: '',
    permissions: [] as string[],
});

const updateForm = useForm({
    name: '',
    permissions: [] as string[],
});

const totalPermissions = computed(() =>
    props.permissionGroups.reduce((total, group) => total + group.permissions.length, 0),
);

function applyRoleSearch(): void {
    router.get(
        '/admin/users',
        {
            ...props.filters,
            role_search: roleFilters.role_search,
            tab: 'roles',
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function resetRoleSearch(): void {
    roleFilters.role_search = '';
    applyRoleSearch();
}

function togglePermission(form: typeof createForm | typeof updateForm, permission: string): void {
    form.permissions = form.permissions.includes(permission)
        ? form.permissions.filter((item) => item !== permission)
        : [...form.permissions, permission];
}

function toggleGroup(form: typeof createForm | typeof updateForm, permissions: string[]): void {
    const allSelected = permissions.every((permission) => form.permissions.includes(permission));
    form.permissions = allSelected
        ? form.permissions.filter((permission) => !permissions.includes(permission))
        : Array.from(new Set([...form.permissions, ...permissions]));
}

function createRole(): void {
    createForm.post('/admin/roles?tab=roles', {
        preserveScroll: true,
        onSuccess: () => createForm.reset(),
    });
}

function startEdit(role: ManagedRole): void {
    editingRole.value = role;
    updateForm.defaults({
        name: role.label,
        permissions: [...role.permissions],
    });
    updateForm.reset();
    updateForm.clearErrors();
}

function updateRole(): void {
    if (!editingRole.value) {
        return;
    }

    updateForm.put(`/admin/roles/${editingRole.value.id}?tab=roles`, {
        preserveScroll: true,
        onSuccess: () => {
            editingRole.value = null;
        },
    });
}

function deleteRole(): void {
    if (!deleteTarget.value) {
        return;
    }

    router.delete(`/admin/roles/${deleteTarget.value.id}?tab=roles`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteTarget.value = null;
        },
    });
}
</script>

<template>
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
        <section
            class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <div
                class="flex flex-col gap-4 border-b border-slate-200 p-5 dark:border-slate-800 md:flex-row md:items-start md:justify-between"
            >
                <div>
                    <h2 class="text-base font-semibold text-slate-950 dark:text-slate-50">Roles</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Control what each access group can view or change.
                    </p>
                </div>
                <form class="flex gap-2" @submit.prevent="applyRoleSearch">
                    <div class="relative">
                        <Search
                            class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        />
                        <input
                            v-model="roleFilters.role_search"
                            class="focus-ring h-10 w-full rounded-md border border-slate-200 bg-white pl-9 pr-3 text-sm text-slate-950 transition placeholder:text-slate-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-50 md:w-72"
                            type="search"
                            placeholder="Search roles"
                        />
                    </div>
                    <AppButton type="submit" variant="secondary">Search</AppButton>
                    <button
                        type="button"
                        class="focus-ring inline-flex size-10 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-slate-100"
                        aria-label="Reset role search"
                        @click="resetRoleSearch"
                    >
                        <X class="size-4" />
                    </button>
                </form>
            </div>

            <div v-if="roles.data.length" class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead
                        class="border-b border-slate-200 bg-slate-50 text-xs font-medium uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400"
                    >
                        <tr>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">Permissions</th>
                            <th class="px-5 py-3">Users</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr
                            v-for="role in roles.data"
                            :key="role.id"
                            class="transition hover:bg-slate-50/80 dark:hover:bg-slate-950/70"
                        >
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="grid size-10 place-items-center rounded-md"
                                        :class="
                                            role.protected
                                                ? 'bg-purple-50 text-purple-700 dark:bg-purple-950 dark:text-purple-300'
                                                : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-200'
                                        "
                                    >
                                        <LockKeyhole v-if="role.protected" class="size-5" />
                                        <ShieldCheck v-else class="size-5" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-950 dark:text-slate-50">{{ role.label }}</p>
                                        <p class="font-mono text-xs text-slate-400">{{ role.name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    <span
                                        v-for="permission in role.permissions.slice(0, 4)"
                                        :key="permission"
                                        class="rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                    >
                                        {{ permission }}
                                    </span>
                                    <span
                                        v-if="role.permissions.length > 4"
                                        class="rounded-full bg-purple-50 px-2 py-1 text-xs text-purple-700 dark:bg-purple-950 dark:text-purple-300"
                                    >
                                        +{{ role.permissions.length - 4 }}
                                    </span>
                                    <span v-if="role.permissions.length === 0" class="text-xs text-slate-400">No permissions</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ role.usersCount }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-1.5">
                                    <button
                                        v-if="role.canEdit"
                                        type="button"
                                        class="focus-ring inline-flex size-9 items-center justify-center rounded-md text-slate-500 transition hover:bg-purple-50 hover:text-purple-700 dark:hover:bg-purple-950 dark:hover:text-purple-300"
                                        :aria-label="`Edit ${role.label}`"
                                        @click="startEdit(role)"
                                    >
                                        <Pencil class="size-4" />
                                    </button>
                                    <button
                                        v-if="role.canDelete"
                                        type="button"
                                        class="focus-ring inline-flex size-9 items-center justify-center rounded-md text-slate-500 transition hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-950 dark:hover:text-red-300"
                                        :aria-label="`Delete ${role.label}`"
                                        @click="deleteTarget = role"
                                    >
                                        <Trash2 class="size-4" />
                                    </button>
                                    <span
                                        v-if="!role.canEdit && !role.canDelete"
                                        class="text-xs text-slate-400 dark:text-slate-500"
                                    >
                                        {{ role.protected ? 'Protected' : 'Restricted' }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="px-6 py-14 text-center">
                <p class="font-medium text-slate-950 dark:text-slate-50">No roles found</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Create a role or clear the search.</p>
            </div>

            <PaginationBar :links="roles.links" :meta="roles.meta" />
        </section>

        <aside class="space-y-6">
            <section
                class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="mb-5 flex items-center gap-3">
                    <div
                        class="grid size-10 place-items-center rounded-md bg-purple-50 text-purple-700 dark:bg-purple-950 dark:text-purple-300"
                    >
                        <Plus class="size-5" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-slate-950 dark:text-slate-50">Create role</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            {{ totalPermissions }} permissions available.
                        </p>
                    </div>
                </div>
                <form class="space-y-5" @submit.prevent="createRole">
                    <FormField
                        label="Role name"
                        for-id="create_role_name"
                        :error="createForm.errors.name"
                        helper="Example: Payroll Reviewer"
                    >
                        <TextInput id="create_role_name" v-model="createForm.name" required />
                    </FormField>
                    <div class="space-y-3">
                        <p class="text-sm font-medium text-slate-800 dark:text-slate-100">Permissions</p>
                        <div
                            v-for="group in permissionGroups"
                            :key="group.group"
                            class="rounded-md border border-slate-200 p-3 dark:border-slate-800"
                        >
                            <label class="flex cursor-pointer items-center justify-between gap-3">
                                <span class="text-sm font-medium text-slate-800 dark:text-slate-100">{{
                                    group.group
                                }}</span>
                                <input
                                    type="checkbox"
                                    class="size-4 rounded border-slate-300 text-purple-700"
                                    :checked="
                                        group.permissions.every((permission) =>
                                            createForm.permissions.includes(permission.value),
                                        )
                                    "
                                    @change="
                                        toggleGroup(
                                            createForm,
                                            group.permissions.map((permission) => permission.value),
                                        )
                                    "
                                />
                            </label>
                            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                <label
                                    v-for="permission in group.permissions"
                                    :key="permission.value"
                                    class="flex cursor-pointer items-center gap-2 text-sm text-slate-600 dark:text-slate-300"
                                >
                                    <input
                                        type="checkbox"
                                        class="size-4 rounded border-slate-300 text-purple-700"
                                        :checked="createForm.permissions.includes(permission.value)"
                                        @change="togglePermission(createForm, permission.value)"
                                    />
                                    {{ permission.label }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <AppButton type="submit" class="w-full" :loading="createForm.processing">Create role</AppButton>
                </form>
            </section>

            <section
                class="rounded-lg border border-slate-200 bg-white p-5 text-sm leading-6 text-slate-600 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
            >
                Keep roles narrow and job-based. Avoid creating broad administrator-like roles unless the business
                workflow truly needs them.
            </section>
        </aside>

        <Transition name="drawer-panel">
            <div
                v-if="editingRole"
                class="fixed inset-0 z-40 flex justify-end bg-slate-950/35 p-4 backdrop-blur-sm"
                @click.self="editingRole = null"
            >
                <section
                    class="flex h-full w-full max-w-2xl flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-950"
                >
                    <div
                        class="flex items-start justify-between gap-4 border-b border-slate-200 p-5 dark:border-slate-800"
                    >
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-purple-700 dark:text-purple-300">
                                Role permissions
                            </p>
                            <h2 class="mt-1 text-lg font-semibold text-slate-950 dark:text-slate-50">Edit role</h2>
                            <p class="mt-1 font-mono text-xs text-slate-400">{{ editingRole.name }}</p>
                        </div>
                        <button
                            type="button"
                            class="focus-ring inline-flex size-9 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 dark:hover:bg-slate-900"
                            aria-label="Close role edit form"
                            @click="editingRole = null"
                        >
                            <X class="size-4" />
                        </button>
                    </div>
                    <form class="flex min-h-0 flex-1 flex-col" @submit.prevent="updateRole">
                        <div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-5">
                            <FormField label="Role name" for-id="edit_role_name" :error="updateForm.errors.name">
                                <TextInput id="edit_role_name" v-model="updateForm.name" required />
                            </FormField>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-100">Permissions</p>
                                    <span
                                        class="rounded-full bg-purple-50 px-2.5 py-1 text-xs font-medium text-purple-700 dark:bg-purple-950 dark:text-purple-300"
                                    >
                                        {{ updateForm.permissions.length }} selected
                                    </span>
                                </div>
                                <div
                                    v-for="group in permissionGroups"
                                    :key="group.group"
                                    class="rounded-md border border-slate-200 p-3 dark:border-slate-800"
                                >
                                    <label class="flex cursor-pointer items-center justify-between gap-3">
                                        <span class="text-sm font-medium text-slate-800 dark:text-slate-100">{{
                                            group.group
                                        }}</span>
                                        <input
                                            type="checkbox"
                                            class="size-4 rounded border-slate-300 text-purple-700"
                                            :checked="
                                                group.permissions.every((permission) =>
                                                    updateForm.permissions.includes(permission.value),
                                                )
                                            "
                                            @change="
                                                toggleGroup(
                                                    updateForm,
                                                    group.permissions.map((permission) => permission.value),
                                                )
                                            "
                                        />
                                    </label>
                                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                        <label
                                            v-for="permission in group.permissions"
                                            :key="permission.value"
                                            class="flex cursor-pointer items-center gap-2 text-sm text-slate-600 dark:text-slate-300"
                                        >
                                            <input
                                                type="checkbox"
                                                class="size-4 rounded border-slate-300 text-purple-700"
                                                :checked="updateForm.permissions.includes(permission.value)"
                                                @change="togglePermission(updateForm, permission.value)"
                                            />
                                            {{ permission.label }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900/70"
                        >
                            <AppButton type="button" variant="secondary" @click="editingRole = null">Cancel</AppButton>
                            <AppButton type="submit" :loading="updateForm.processing">Save role</AppButton>
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
                    <h2 class="text-base font-semibold text-slate-950 dark:text-slate-50">Delete role?</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        {{ deleteTarget.label }} will be removed from RBAC. Roles assigned to users cannot be deleted.
                    </p>
                    <div class="mt-5 flex justify-end gap-2">
                        <AppButton type="button" variant="secondary" @click="deleteTarget = null">Cancel</AppButton>
                        <AppButton type="button" variant="danger" @click="deleteRole">
                            <Trash2 class="size-4" />
                            Delete role
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
