import type { PaginatedResponse } from '@/types/access';
import type { SelectOption } from '@/types';

export interface AuditValue {
    label: string;
    value: string;
}

export interface AuditChange {
    field: string;
    from: string;
    to: string;
}

export interface AuditParty {
    name?: string | null;
    email?: string | null;
    label?: string | null;
    detail?: string | null;
    type: string;
}

export interface AuditActivity {
    id: number;
    module: string;
    moduleKey?: string | null;
    event?: string | null;
    eventLabel: string;
    description: string;
    actor: AuditParty;
    subject: AuditParty;
    changes: AuditChange[];
    meta: AuditValue[];
    status: 'success' | 'warning' | 'failed';
    occurredAt?: string | null;
    occurredAtDisplay?: string | null;
    relativeTime?: string | null;
}

export interface AuditFilters {
    search?: string;
    module?: string;
    event?: string;
    actor?: string;
    date_from?: string | null;
    date_to?: string | null;
    per_page?: number;
}

export interface AuditSummary {
    total: number;
    today: number;
    security: number;
    writes: number;
}

export interface AuditPageProps {
    activities: PaginatedResponse<AuditActivity>;
    filters: AuditFilters;
    moduleOptions: SelectOption[];
    eventOptions: SelectOption[];
    summary: AuditSummary;
}
