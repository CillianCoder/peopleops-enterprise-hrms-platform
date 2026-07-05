<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, shallowRef, watch } from 'vue';
import AccessTabs from '@/Components/Admin/Access/AccessTabs.vue';
import CredentialDialog from '@/Components/Admin/Access/CredentialDialog.vue';
import RolesPanel from '@/Components/Admin/Access/RolesPanel.vue';
import UsersPanel from '@/Components/Admin/Access/UsersPanel.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { SelectOption, SharedProps } from '@/types';
import type { AccessFilters, ManagedRole, ManagedUser, PaginatedResponse, PermissionGroup } from '@/types/access';

const props = defineProps<{
    users: PaginatedResponse<ManagedUser>;
    roles: PaginatedResponse<ManagedRole>;
    filters: AccessFilters;
    assignableRoles: SelectOption[];
    statusOptions: SelectOption[];
    permissionGroups: PermissionGroup[];
}>();

const page = usePage<SharedProps>();
const activeTab = shallowRef<'users' | 'roles'>(props.filters.tab === 'roles' ? 'roles' : 'users');
const credentialDialogOpen = shallowRef(false);

const createdLogin = computed(() => page.props.flash.createdLogin ?? null);

watch(
    createdLogin,
    (credential) => {
        if (credential) {
            credentialDialogOpen.value = true;
        }
    },
    { immediate: true },
);
</script>

<template>
    <AppLayout title="Users & RBAC" description="Manage administrator-created users, role assignments, and permission groups from one controlled access workspace.">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-purple-700 dark:text-purple-300">Admin access</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950 dark:text-slate-50">Users and permissions</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                    Keep access tidy: create named users, assign focused roles, and protect high-risk permissions from casual changes.
                </p>
            </div>
            <AccessTabs v-model="activeTab" />
        </div>

        <Transition name="tab-panel" mode="out-in">
            <UsersPanel
                v-if="activeTab === 'users'"
                key="users"
                :users="users"
                :filters="filters"
                :assignable-roles="assignableRoles"
                :status-options="statusOptions"
                @created="credentialDialogOpen = true"
            />
            <RolesPanel
                v-else
                key="roles"
                :roles="roles"
                :filters="filters"
                :permission-groups="permissionGroups"
            />
        </Transition>

        <CredentialDialog v-model="credentialDialogOpen" :credential="createdLogin" />
    </AppLayout>
</template>

<style scoped>
.tab-panel-enter-active,
.tab-panel-leave-active {
    transition:
        opacity 180ms ease,
        transform 180ms ease;
}

.tab-panel-enter-from {
    opacity: 0;
    transform: translateY(8px);
}

.tab-panel-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
</style>
