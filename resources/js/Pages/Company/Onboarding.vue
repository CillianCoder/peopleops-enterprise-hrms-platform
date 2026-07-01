<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Building2, ImageUp } from '@lucide/vue';
import AppButton from '@/Components/AppButton.vue';
import FormField from '@/Components/FormField.vue';
import TextInput from '@/Components/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { SelectOption } from '@/types';

interface CompanyPayload {
    name?: string;
    legal_name?: string;
    industry?: string;
    registration_number?: string | null;
    tax_number?: string | null;
    email?: string;
    phone?: string;
    website?: string | null;
    address_line_1?: string;
    address_line_2?: string | null;
    city?: string;
    state?: string | null;
    postal_code?: string | null;
    country?: string;
    timezone?: string;
    currency?: string;
}

const props = defineProps<{
    company: CompanyPayload | null;
    industries: SelectOption[];
}>();

const form = useForm({
    name: props.company?.name ?? '',
    legal_name: props.company?.legal_name ?? '',
    industry: props.company?.industry ?? 'software',
    registration_number: props.company?.registration_number ?? '',
    tax_number: props.company?.tax_number ?? '',
    email: props.company?.email ?? '',
    phone: props.company?.phone ?? '',
    website: props.company?.website ?? '',
    address_line_1: props.company?.address_line_1 ?? '',
    address_line_2: props.company?.address_line_2 ?? '',
    city: props.company?.city ?? '',
    state: props.company?.state ?? '',
    postal_code: props.company?.postal_code ?? '',
    country: props.company?.country ?? 'LK',
    timezone: props.company?.timezone ?? 'Asia/Colombo',
    currency: props.company?.currency ?? 'LKR',
    logo: null as File | null,
});

function setLogo(event: Event): void {
    const target = event.target as HTMLInputElement;
    form.logo = target.files?.[0] ?? null;
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post('/company/onboarding', {
        forceFormData: true,
    });
}
</script>

<template>
    <AppLayout
        title="Company onboarding"
        description="Complete the required company profile before opening the dashboard. These details power branding, documents, calendars, payroll defaults, and audit context."
    >
        <form class="grid gap-6 xl:grid-cols-[1fr_360px]" @submit.prevent="submit">
            <div class="space-y-6">
                <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-5 flex items-center gap-3">
                        <Building2 class="size-5 text-purple-700" />
                        <h2 class="text-base font-semibold text-slate-950 dark:text-slate-50">Company identity</h2>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <FormField label="Trading name" for-id="name" :error="form.errors.name">
                            <TextInput id="name" v-model="form.name" required />
                        </FormField>
                        <FormField label="Legal name" for-id="legal_name" :error="form.errors.legal_name">
                            <TextInput id="legal_name" v-model="form.legal_name" required />
                        </FormField>
                        <FormField label="Industry" for-id="industry" :error="form.errors.industry">
                            <select
                                id="industry"
                                v-model="form.industry"
                                class="focus-ring h-11 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-950 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-50"
                            >
                                <option v-for="industry in industries" :key="industry.value" :value="industry.value">
                                    {{ industry.label }}
                                </option>
                            </select>
                        </FormField>
                        <FormField label="Website" for-id="website" :error="form.errors.website">
                            <TextInput id="website" v-model="form.website" placeholder="https://example.com" />
                        </FormField>
                        <FormField label="Registration number" for-id="registration_number" :error="form.errors.registration_number">
                            <TextInput id="registration_number" v-model="form.registration_number" />
                        </FormField>
                        <FormField label="Tax number" for-id="tax_number" :error="form.errors.tax_number">
                            <TextInput id="tax_number" v-model="form.tax_number" />
                        </FormField>
                    </div>
                </section>

                <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="mb-5 text-base font-semibold text-slate-950 dark:text-slate-50">Contact and location</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <FormField label="Company email" for-id="email" :error="form.errors.email">
                            <TextInput id="email" v-model="form.email" type="email" required />
                        </FormField>
                        <FormField label="Phone" for-id="phone" :error="form.errors.phone">
                            <TextInput id="phone" v-model="form.phone" required />
                        </FormField>
                        <FormField label="Address line 1" for-id="address_line_1" :error="form.errors.address_line_1">
                            <TextInput id="address_line_1" v-model="form.address_line_1" required />
                        </FormField>
                        <FormField label="Address line 2" for-id="address_line_2" :error="form.errors.address_line_2">
                            <TextInput id="address_line_2" v-model="form.address_line_2" />
                        </FormField>
                        <FormField label="City" for-id="city" :error="form.errors.city">
                            <TextInput id="city" v-model="form.city" required />
                        </FormField>
                        <FormField label="State or province" for-id="state" :error="form.errors.state">
                            <TextInput id="state" v-model="form.state" />
                        </FormField>
                        <FormField label="Postal code" for-id="postal_code" :error="form.errors.postal_code">
                            <TextInput id="postal_code" v-model="form.postal_code" />
                        </FormField>
                        <FormField label="Country code" for-id="country" :error="form.errors.country" helper="Use ISO 3166-1 alpha-2, for example LK or US.">
                            <TextInput id="country" v-model="form.country" required />
                        </FormField>
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="mb-5 text-base font-semibold text-slate-950 dark:text-slate-50">Defaults</h2>
                    <div class="space-y-4">
                        <FormField label="Timezone" for-id="timezone" :error="form.errors.timezone">
                            <TextInput id="timezone" v-model="form.timezone" required />
                        </FormField>
                        <FormField label="Currency" for-id="currency" :error="form.errors.currency">
                            <TextInput id="currency" v-model="form.currency" required />
                        </FormField>
                    </div>
                </section>

                <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-4 flex items-center gap-3">
                        <ImageUp class="size-5 text-purple-700" />
                        <h2 class="text-base font-semibold text-slate-950 dark:text-slate-50">Company logo</h2>
                    </div>
                    <label
                        for="logo"
                        class="focus-ring flex cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-950 dark:hover:bg-slate-800"
                    >
                        <ImageUp class="mb-3 size-8 text-slate-400" />
                        <span class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ form.logo?.name ?? 'Upload logo' }}</span>
                        <span class="mt-1 text-xs text-slate-500 dark:text-slate-400">PNG, JPG, WebP, or SVG up to 2 MB</span>
                    </label>
                    <input id="logo" class="sr-only" type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml" @change="setLogo" />
                    <p v-if="form.errors.logo" class="mt-2 text-sm text-red-600">{{ form.errors.logo }}</p>
                </section>

                <AppButton type="submit" class="w-full" :loading="form.processing">Complete company setup</AppButton>
            </aside>
        </form>
    </AppLayout>
</template>
