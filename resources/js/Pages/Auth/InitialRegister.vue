<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import AppButton from '@/Components/AppButton.vue';
import FormField from '@/Components/FormField.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit(): void {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <AuthLayout
        title="Create the first administrator"
        description="This signup is available only while the system has zero users. The first account becomes the single system administrator."
    >
        <form class="space-y-5" @submit.prevent="submit">
            <FormField label="Full name" for-id="name" :error="form.errors.name">
                <TextInput id="name" v-model="form.name" autocomplete="name" required />
            </FormField>
            <FormField label="Work email" for-id="email" :error="form.errors.email">
                <TextInput id="email" v-model="form.email" type="email" autocomplete="username" required />
            </FormField>
            <FormField label="Password" for-id="password" :error="form.errors.password" helper="Use at least 12 characters with mixed case, numbers, and symbols.">
                <TextInput id="password" v-model="form.password" type="password" autocomplete="new-password" required />
            </FormField>
            <FormField label="Confirm password" for-id="password_confirmation" :error="form.errors.password_confirmation">
                <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password" required />
            </FormField>
            <AppButton type="submit" class="w-full" :loading="form.processing">Create administrator</AppButton>
        </form>
    </AuthLayout>
</template>
