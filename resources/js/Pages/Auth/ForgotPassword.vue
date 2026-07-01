<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import AppButton from '@/Components/AppButton.vue';
import FormField from '@/Components/FormField.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const form = useForm({
    email: '',
});

function submit(): void {
    form.post('/forgot-password');
}
</script>

<template>
    <AuthLayout title="Reset your password" description="Enter your work email and PeopleOps will send a secure password reset link.">
        <form class="space-y-5" @submit.prevent="submit">
            <FormField label="Email address" for-id="email" :error="form.errors.email">
                <TextInput id="email" v-model="form.email" type="email" autocomplete="username" required />
            </FormField>
            <AppButton type="submit" class="w-full" :loading="form.processing">Send reset link</AppButton>
            <Link href="/login" class="block text-center text-sm font-medium text-purple-700 hover:text-purple-800 dark:text-purple-300 dark:hover:text-purple-200">Back to sign in</Link>
        </form>
    </AuthLayout>
</template>
