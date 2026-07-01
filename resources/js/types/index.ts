export interface AuthUser {
    id: number;
    name: string;
    email: string;
    jobTitle?: string | null;
    roles: string[];
    permissions: string[];
    companyReady: boolean;
}

export interface SharedProps {
    [key: string]: unknown;
    auth: {
        user: AuthUser | null;
    };
    company: {
        id: number;
        name: string;
        logoPath?: string | null;
        logoUrl?: string | null;
    } | null;
    flash: {
        success?: string | null;
        error?: string | null;
    };
}

export interface SelectOption {
    value: string;
    label: string;
}

export interface Paginated<T> {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    meta?: Record<string, unknown>;
}
