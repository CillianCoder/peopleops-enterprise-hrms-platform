import type { SelectOption } from '@/types';

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginationMeta {
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
}

export interface PaginatedResponse<T> {
    data: T[];
    links: PaginationLink[];
    meta: PaginationMeta;
}

export interface ManagedUser {
    id: number;
    name: string;
    email: string;
    nic?: string | null;
    jobTitle?: string | null;
    phone?: string | null;
    status: 'active' | 'suspended';
    role?: string | null;
    roleLabel?: string | null;
    isSuperAdmin: boolean;
    canEdit: boolean;
    canDelete: boolean;
    createdAt?: string | null;
}

export interface ManagedRole {
    id: number;
    name: string;
    label: string;
    usersCount: number;
    permissions: string[];
    protected: boolean;
    canEdit: boolean;
    canDelete: boolean;
}

export interface PermissionGroup {
    group: string;
    permissions: SelectOption[];
}

export interface AccessFilters {
    search?: string;
    status?: string;
    role?: string;
    per_page?: number;
    role_search?: string;
    tab?: 'users' | 'roles';
}
