<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import AppButton from '@/Components/AppButton.vue';
import FormField from '@/Components/FormField.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

function submit(): void {
    form.post('/reset-password', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <AuthLayout title="Choose a new password" description="Set a strong password before returning to your PeopleOps workspace.">
        <form class="space-y-5" @submit.prevent="submit">
            <FormField label="Email address" for-id="email" :error="form.errors.email">
                <TextInput id="email" v-model="form.email" type="email" autocomplete="username" required />
            </FormField>
            <FormField label="New password" for-id="password" :error="form.errors.password">
                <TextInput id="password" v-model="form.password" type="password" autocomplete="new-password" required />
            </FormField>
            <FormField label="Confirm new password" for-id="password_confirmation" :error="form.errors.password_confirmation">
                <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password" required />
            </FormField>
            <AppButton type="submit" class="w-full" :loading="form.processing">Reset password</AppButton>
        </form>
    </AuthLayout>
</template>
