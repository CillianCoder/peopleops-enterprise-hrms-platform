<script setup lang="ts">
import { Check, Copy, KeyRound, Mail, X } from '@lucide/vue';
import { shallowRef } from 'vue';
import { toast } from 'vue-sonner';
import AppButton from '@/Components/AppButton.vue';

const open = defineModel<boolean>({ required: true });

defineProps<{
    credential?: {
        name: string;
        email: string;
        password: string;
    } | null;
}>();

const copiedField = shallowRef<string | null>(null);

async function copyText(label: string, value: string): Promise<void> {
    await globalThis.navigator.clipboard.writeText(value);
    copiedField.value = label;
    toast.success(`${label} copied`);
    globalThis.setTimeout(() => {
        copiedField.value = null;
    }, 1500);
}

function close(): void {
    open.value = false;
}
</script>

<template>
    <Transition name="dialog-fade">
        <div v-if="open && credential" class="fixed inset-0 z-50 grid place-items-center bg-slate-950/40 p-4 backdrop-blur-sm" role="presentation">
            <section
                class="w-full max-w-lg rounded-lg border border-slate-200 bg-white shadow-2xl shadow-slate-950/15 dark:border-slate-800 dark:bg-slate-950"
                role="dialog"
                aria-modal="true"
                aria-labelledby="credential-dialog-title"
            >
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 p-5 dark:border-slate-800">
                    <div class="flex gap-3">
                        <div class="grid size-10 place-items-center rounded-md bg-purple-50 text-purple-700 dark:bg-purple-950 dark:text-purple-300">
                            <KeyRound class="size-5" />
                        </div>
                        <div>
                            <h2 id="credential-dialog-title" class="text-base font-semibold text-slate-950 dark:text-slate-50">Login is ready</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                Share these details through an approved private channel. This password is shown only now.
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="focus-ring inline-flex size-9 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-900 dark:hover:text-slate-100"
                        aria-label="Close credential dialog"
                        @click="close"
                    >
                        <X class="size-4" />
                    </button>
                </div>

                <div class="space-y-3 p-5">
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/70">
                        <p class="text-sm font-medium text-slate-950 dark:text-slate-50">{{ credential.name }}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ credential.email }}</p>
                    </div>

                    <button
                        type="button"
                        class="focus-ring group flex w-full items-center justify-between gap-3 rounded-md border border-slate-200 bg-white px-4 py-3 text-left transition hover:-translate-y-0.5 hover:border-purple-200 hover:shadow-sm dark:border-slate-800 dark:bg-slate-950 dark:hover:border-purple-900"
                        @click="copyText('Email', credential.email)"
                    >
                        <span class="flex items-center gap-3">
                            <Mail class="size-4 text-slate-400" />
                            <span>
                                <span class="block text-xs font-medium uppercase text-slate-400">Email</span>
                                <span class="block text-sm text-slate-800 dark:text-slate-100">{{ credential.email }}</span>
                            </span>
                        </span>
                        <Check v-if="copiedField === 'Email'" class="size-4 text-emerald-600" />
                        <Copy v-else class="size-4 text-slate-400 transition group-hover:text-purple-700" />
                    </button>

                    <button
                        type="button"
                        class="focus-ring group flex w-full items-center justify-between gap-3 rounded-md border border-slate-200 bg-white px-4 py-3 text-left transition hover:-translate-y-0.5 hover:border-purple-200 hover:shadow-sm dark:border-slate-800 dark:bg-slate-950 dark:hover:border-purple-900"
                        @click="copyText('Password', credential.password)"
                    >
                        <span class="flex items-center gap-3">
                            <KeyRound class="size-4 text-slate-400" />
                            <span>
                                <span class="block text-xs font-medium uppercase text-slate-400">Temporary password</span>
                                <span class="block font-mono text-sm text-slate-800 dark:text-slate-100">{{ credential.password }}</span>
                            </span>
                        </span>
                        <Check v-if="copiedField === 'Password'" class="size-4 text-emerald-600" />
                        <Copy v-else class="size-4 text-slate-400 transition group-hover:text-purple-700" />
                    </button>
                </div>

                <div class="flex flex-col gap-2 border-t border-slate-200 p-5 dark:border-slate-800 sm:flex-row sm:justify-end">
                    <AppButton type="button" variant="secondary" @click="copyText('Login details', `Email: ${credential.email}\nPassword: ${credential.password}`)">
                        <Copy class="size-4" />
                        Copy all
                    </AppButton>
                    <AppButton type="button" @click="close">Done</AppButton>
                </div>
            </section>
        </div>
    </Transition>
</template>

<style scoped>
.dialog-fade-enter-active,
.dialog-fade-leave-active {
    transition:
        opacity 180ms ease,
        transform 180ms ease;
}

.dialog-fade-enter-from,
.dialog-fade-leave-to {
    opacity: 0;
    transform: translateY(8px) scale(0.98);
}
</style>
