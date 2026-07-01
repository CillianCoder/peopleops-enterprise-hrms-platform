<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import AppButton from '@/Components/AppButton.vue';
import FormField from '@/Components/FormField.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

defineProps<{
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit(): void {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <AuthLayout title="Sign in to PeopleOps" description="Use your managed company account to access the HR workspace.">
        <form class="space-y-5" @submit.prevent="submit">
            <FormField label="Email address" for-id="email" :error="form.errors.email">
                <TextInput id="email" v-model="form.email" type="email" autocomplete="username" required />
            </FormField>

            <FormField label="Password" for-id="password" :error="form.errors.password">
                <TextInput id="password" v-model="form.password" type="password" autocomplete="current-password" required />
            </FormField>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <input v-model="form.remember" class="size-4 rounded border-slate-300 text-purple-700" type="checkbox" />
                    Remember this device
                </label>
                <Link v-if="canResetPassword" href="/forgot-password" class="text-sm font-medium text-purple-700 hover:text-purple-800 dark:text-purple-300 dark:hover:text-purple-200">
                    Reset password
                </Link>
            </div>

            <AppButton type="submit" class="w-full" :loading="form.processing">Sign in</AppButton>
        </form>
    </AuthLayout>
</template>
