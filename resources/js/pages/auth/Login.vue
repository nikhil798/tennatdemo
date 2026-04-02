<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const props = defineProps<{
    status?: string;
    canResetPassword: boolean;
    showTenantSelector: boolean;
    selectedTenantId: number | null;
    tenants: Array<{
        id: number;
        name: string;
        domain: string;
        database: string;
        status: string;
        available: boolean;
    }>;
}>();

const form = useForm({
    tenant_id: props.selectedTenantId,
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase title="Log in to your account" description="Enter your email and password below to log in">
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div v-if="showTenantSelector" class="grid gap-2">
                    <Label for="tenant_id">Workspace</Label>
                    <select
                        id="tenant_id"
                        v-model="form.tenant_id"
                        required
                        tabindex="1"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background"
                    >
                        <option :value="null" disabled>Select a workspace</option>
                        <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id" :disabled="!tenant.available">
                            {{ tenant.name }} ({{ tenant.available ? tenant.database : 'database missing' }}, {{ tenant.status }})
                        </option>
                    </select>
                    <p class="text-xs text-muted-foreground">Choose the user workspace first so login uses that tenant database. Workspaces with missing databases are disabled.</p>
                    <InputError :message="form.errors.tenant_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :autofocus="!showTenantSelector"
                        :tabindex="showTenantSelector ? 2 : 1"
                        autocomplete="email"
                        v-model="form.email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm" tabindex="5"> Forgot password? </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="showTenantSelector ? 3 : 2"
                        autocomplete="current-password"
                        v-model="form.password"
                        placeholder="Password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between" :tabindex="showTenantSelector ? 4 : 3">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" v-model:checked="form.remember" :tabindex="showTenantSelector ? 5 : 4" />
                        <span>Remember me</span>
                    </Label>
                </div>

                <Button type="submit" class="mt-4 w-full" :tabindex="showTenantSelector ? 6 : 4" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Log in
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Don't have an account?
                <TextLink :href="route('register')" :tabindex="showTenantSelector ? 7 : 5">Sign up</TextLink>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Main platform user?
                <TextLink :href="route('landlord.login')">Landlord login</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
