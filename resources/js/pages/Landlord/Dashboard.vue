<script setup lang="ts">
import { Button } from '@/components/ui/button';
import LandlordLayout from '@/layouts/LandlordLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    landlordUser?: {
        name?: string | null;
        email?: string | null;
    };
    stats: {
        tenants: number;
        plans: number;
        subscriptions: number;
        subscribers: number;
        global_settings: number;
        tenant_users: number;
    };
    tenants: Array<{
        id: number;
        name: string;
        domain: string;
        database: string;
        status: string;
        subscriber?: string | null;
        plan?: string | null;
    }>;
    plans: Array<{
        name: string;
        code: string;
        price: string;
        currency: string;
        billing_cycle: string;
        trial_days: number;
        is_active: boolean;
    }>;
    settings: Array<{
        key: string;
        value?: string | null;
        type: string;
    }>;
    tenantUserOverview: Array<{
        tenant_id: number;
        tenant_name: string;
        tenant_domain: string;
        database: string;
        database_available: boolean;
        user_count: number;
        recent_users: Array<{
            name: string;
            email: string;
        }>;
    }>;
}>();

const overviewCards = computed(() => [
    { label: 'Tenants', value: props.stats.tenants, tone: 'bg-amber-50 text-amber-800 ring-amber-200' },
    { label: 'Plans', value: props.stats.plans, tone: 'bg-emerald-50 text-emerald-800 ring-emerald-200' },
    { label: 'Subscriptions', value: props.stats.subscriptions, tone: 'bg-sky-50 text-sky-800 ring-sky-200' },
    { label: 'Subscribers', value: props.stats.subscribers, tone: 'bg-rose-50 text-rose-800 ring-rose-200' },
    { label: 'Tenant Users', value: props.stats.tenant_users, tone: 'bg-indigo-50 text-indigo-800 ring-indigo-200' },
    { label: 'Global Settings', value: props.stats.global_settings, tone: 'bg-violet-50 text-violet-800 ring-violet-200' },
]);

const architectureZones = [
    {
        title: 'Landlord Database',
        description: 'Stores shared platform records and stays the system control plane.',
        items: ['Tenants', 'Plans', 'Subscriptions', 'Subscribers', 'Global settings'],
    },
    {
        title: 'Tenant Database',
        description: 'Each workspace gets its own isolated MySQL database selected at runtime.',
        items: ['Users', 'Tenant business data', 'Workspace tables', 'Tenant-side auth records'],
    },
];

const requestFlow = [
    'Landlord provisions a tenant and creates a dedicated MySQL database.',
    'Tenant metadata and subscription records remain in the landlord database.',
    'The middleware resolves the workspace from the domain or selected tenant context.',
    'SwitchDatabase rewires Laravel to the tenant connection before auth and tenant models run.',
    'The request uses isolated tenant data while billing and global config remain landlord-owned.',
];
</script>

<template>
    <LandlordLayout
        title="Landlord Control Plane"
        description="A visual overview of how the landlord database and tenant databases work together in this custom multi-tenancy setup."
    >
        <template #hero>
            <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                <p class="text-xs font-semibold uppercase tracking-[0.26em] text-amber-300">Logged In As Landlord</p>
                <p class="mt-3 text-2xl font-semibold text-white">{{ landlordUser?.name ?? 'Landlord user' }}</p>
                <p class="mt-1 text-sm text-slate-300">{{ landlordUser?.email ?? 'No email available' }}</p>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="card in overviewCards"
                    :key="card.label"
                    class="rounded-2xl px-4 py-4 ring-1"
                    :class="card.tone"
                >
                    <p class="text-xs font-semibold uppercase tracking-[0.26em]">{{ card.label }}</p>
                    <p class="mt-3 text-3xl font-semibold">{{ card.value }}</p>
                </div>
            </div>
        </template>

        <template #aside>
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-700">How It Works</p>
            <div class="mt-4 space-y-4">
                <div
                    v-for="(step, index) in requestFlow"
                    :key="step"
                    class="flex gap-4 rounded-2xl border border-slate-200 bg-slate-50/80 p-4"
                >
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">
                        {{ index + 1 }}
                    </div>
                    <p class="text-sm leading-6 text-slate-700">{{ step }}</p>
                </div>
            </div>
        </template>

        <div class="grid gap-8 xl:grid-cols-[1.15fr_0.85fr]">
            <section class="space-y-8">
                <div class="rounded-[28px] border border-slate-900/10 bg-white/90 p-6 shadow-[0_20px_70px_rgba(39,29,9,0.08)]">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">System Boundaries</p>
                            <h2 class="mt-2 text-2xl font-semibold text-slate-900">What lives where</h2>
                        </div>
                        <Button as-child variant="outline" class="border-slate-300 bg-white">
                            <Link :href="route('register')">Create Tenant</Link>
                        </Button>
                    </div>

                    <div class="mt-6 grid gap-4 lg:grid-cols-2">
                        <div
                            v-for="zone in architectureZones"
                            :key="zone.title"
                            class="rounded-[24px] border border-slate-200 bg-[linear-gradient(180deg,#ffffff_0%,#f8f6f1_100%)] p-5"
                        >
                            <p class="text-lg font-semibold text-slate-900">{{ zone.title }}</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ zone.description }}</p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span
                                    v-for="item in zone.items"
                                    :key="item"
                                    class="rounded-full bg-slate-900 px-3 py-1 text-xs font-medium text-white"
                                >
                                    {{ item }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-[24px] border border-dashed border-slate-300 bg-slate-50 p-5">
                        <p class="text-sm font-medium text-slate-900">Runtime switching</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            The request begins in landlord context, resolves the tenant, then swaps the active Laravel database
                            connection before authentication and tenant models are touched. That keeps tenant data isolated while
                            platform-level records stay centralized.
                        </p>
                    </div>
                </div>

                <div class="rounded-[28px] border border-slate-900/10 bg-white/90 p-6 shadow-[0_20px_70px_rgba(39,29,9,0.08)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Recent Tenants</p>
                    <div class="mt-5 space-y-3">
                        <div
                            v-for="tenant in tenants"
                            :key="tenant.id"
                            class="rounded-[22px] border border-slate-200 bg-[linear-gradient(180deg,#fffdf8_0%,#f6f1e7_100%)] p-4"
                        >
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <p class="text-lg font-semibold text-slate-900">{{ tenant.name }}</p>
                                    <p class="mt-1 font-mono text-sm text-slate-600">{{ tenant.database }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ tenant.domain }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-medium text-white">
                                        {{ tenant.status }}
                                    </span>
                                    <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-medium text-slate-700">
                                        {{ tenant.plan ?? 'No plan' }}
                                    </span>
                                </div>
                            </div>
                            <p class="mt-3 text-sm text-slate-600">Subscriber: {{ tenant.subscriber ?? 'Not linked yet' }}</p>
                        </div>
                        <p v-if="!tenants.length" class="text-sm text-slate-500">No tenants provisioned yet.</p>
                    </div>
                </div>

                <div class="rounded-[28px] border border-slate-900/10 bg-white/90 p-6 shadow-[0_20px_70px_rgba(39,29,9,0.08)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Tenant Users</p>
                    <div class="mt-5 space-y-3">
                        <div
                            v-for="tenantUsers in tenantUserOverview"
                            :key="tenantUsers.tenant_id"
                            class="rounded-[22px] border border-slate-200 bg-slate-50/80 p-4"
                        >
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ tenantUsers.tenant_name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ tenantUsers.tenant_domain }}</p>
                                    <p class="mt-1 font-mono text-xs text-slate-500">{{ tenantUsers.database }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-medium"
                                        :class="tenantUsers.database_available ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700'"
                                    >
                                        {{ tenantUsers.database_available ? 'Database OK' : 'Database Missing' }}
                                    </span>
                                    <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-medium text-white">
                                        {{ tenantUsers.user_count }} users
                                    </span>
                                </div>
                            </div>

                            <div class="mt-3 space-y-2">
                                <div v-for="user in tenantUsers.recent_users" :key="`${tenantUsers.tenant_id}-${user.email}`" class="rounded-xl bg-white px-3 py-2">
                                    <p class="font-medium text-slate-900">{{ user.name }}</p>
                                    <p class="text-sm text-slate-500">{{ user.email }}</p>
                                </div>
                                <p v-if="!tenantUsers.recent_users.length" class="text-sm text-slate-500">
                                    No tenant users found for this workspace.
                                </p>
                            </div>
                        </div>
                        <p v-if="!tenantUserOverview.length" class="text-sm text-slate-500">No tenant user overview available yet.</p>
                    </div>
                </div>
            </section>

            <section class="space-y-8">
                <div class="rounded-[28px] border border-slate-900/10 bg-white/90 p-6 shadow-[0_20px_70px_rgba(39,29,9,0.08)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Plan Catalog</p>
                    <div class="mt-5 space-y-3">
                        <div
                            v-for="plan in plans"
                            :key="plan.code"
                            class="rounded-[22px] border border-slate-200 bg-slate-50/80 p-4"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ plan.name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ plan.code }}</p>
                                </div>
                                <span
                                    class="rounded-full px-3 py-1 text-xs font-semibold"
                                    :class="plan.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'"
                                >
                                    {{ plan.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <p class="mt-3 text-sm text-slate-600">
                                {{ plan.currency }} {{ plan.price }} / {{ plan.billing_cycle }} with {{ plan.trial_days }} trial days
                            </p>
                        </div>
                        <p v-if="!plans.length" class="text-sm text-slate-500">No plans configured yet.</p>
                    </div>
                </div>

                <div class="rounded-[28px] border border-slate-900/10 bg-white/90 p-6 shadow-[0_20px_70px_rgba(39,29,9,0.08)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Global Settings</p>
                    <div class="mt-5 space-y-3">
                        <div
                            v-for="setting in settings"
                            :key="setting.key"
                            class="rounded-[22px] border border-slate-200 bg-slate-50/80 p-4"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-semibold text-slate-900">{{ setting.key }}</p>
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                    {{ setting.type }}
                                </span>
                            </div>
                            <p class="mt-2 break-all text-sm leading-6 text-slate-600">{{ setting.value ?? 'null' }}</p>
                        </div>
                        <p v-if="!settings.length" class="text-sm text-slate-500">No autoloaded global settings yet.</p>
                    </div>
                </div>
            </section>
        </div>
    </LandlordLayout>
</template>
