<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const props = defineProps<{
    name?: string;
    tenant?: {
        name: string;
        slug: string | null;
        domain: string;
        database: string;
        status: string;
    } | null;
    subscriber?: {
        name: string;
        email: string;
        company?: string | null;
    } | null;
    subscription?: {
        status: string;
        billing_cycle: string;
        amount: string;
        currency: string;
        starts_at?: string | null;
        trial_ends_at?: string | null;
        renews_at?: string | null;
    } | null;
    plan?: {
        name: string;
        code: string;
        price: string;
        currency: string;
        billing_cycle: string;
        features: Record<string, boolean>;
    } | null;
    employeeSummary?: {
        total: number;
        active: number;
        recent: Array<{
            id: number;
            name: string;
            email: string;
            designation?: string | null;
            department?: string | null;
            status: string;
        }>;
    } | null;
    globalConfigCount?: number;
}>();

const requestSteps = computed(() => [
    {
        title: 'Tenant resolved',
        detail: props.tenant?.domain
            ? `The middleware matched this workspace and prepared ${props.tenant.domain} for the request.`
            : 'The middleware resolved the active workspace before auth.',
    },
    {
        title: 'Connection switched',
        detail: props.tenant?.database
            ? `Laravel switched into the isolated tenant database ${props.tenant.database}.`
            : 'Laravel switched into the active tenant database before loading models.',
    },
    {
        title: 'User authenticated',
        detail: props.name
            ? `${props.name} was loaded from the tenant users table, not the landlord database.`
            : 'The authenticated user is read from the tenant users table.',
    },
    {
        title: 'Landlord metadata attached',
        detail: 'Plan, subscription, subscriber, and global settings remain landlord-owned and are attached separately.',
    },
]);

const dataZones = computed(() => [
    {
        label: 'Tenant Database',
        value: props.tenant?.database ?? 'N/A',
        items: ['users', 'tenant app data', 'isolated tables'],
        tone: 'bg-sky-50 border-sky-200',
    },
    {
        label: 'Landlord Database',
        value: props.plan?.name ?? 'Platform control data',
        items: ['plans', 'subscriptions', 'subscribers', 'global settings'],
        tone: 'bg-amber-50 border-amber-200',
    },
]);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid gap-4 lg:grid-cols-3">
                <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <p class="text-sm text-muted-foreground">Signed in as</p>
                    <p class="text-lg font-semibold">{{ name ?? 'Unknown user' }}</p>
                    <p class="mt-4 text-sm text-muted-foreground">Tenant workspace</p>
                    <p class="font-medium">{{ tenant?.name ?? 'No tenant selected' }}</p>
                    <p class="mt-2 text-sm text-muted-foreground">Tenant database</p>
                    <p class="font-mono text-sm">{{ tenant?.database ?? 'No database selected' }}</p>
                    <p class="mt-2 text-sm text-muted-foreground">Tenant status</p>
                    <p class="font-medium capitalize">{{ tenant?.status ?? 'Unknown' }}</p>
                </div>

                <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <p class="text-sm text-muted-foreground">Landlord-managed plan</p>
                    <p class="text-lg font-semibold">{{ plan?.name ?? 'No plan attached' }}</p>
                    <p class="mt-2 text-sm text-muted-foreground">Billing</p>
                    <p class="font-medium">
                        {{ subscription?.currency ?? plan?.currency ?? 'USD' }} {{ subscription?.amount ?? plan?.price ?? '0.00' }}
                        / {{ subscription?.billing_cycle ?? plan?.billing_cycle ?? 'monthly' }}
                    </p>
                    <p class="mt-2 text-sm text-muted-foreground">Subscription status</p>
                    <p class="font-medium capitalize">{{ subscription?.status ?? 'Not subscribed' }}</p>
                    <p class="mt-2 text-sm text-muted-foreground">Renews at</p>
                    <p class="font-medium">{{ subscription?.renews_at ?? 'N/A' }}</p>
                </div>

                <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <p class="text-sm text-muted-foreground">Subscriber</p>
                    <p class="text-lg font-semibold">{{ subscriber?.name ?? 'No subscriber' }}</p>
                    <p class="mt-2 text-sm text-muted-foreground">Email</p>
                    <p class="font-medium">{{ subscriber?.email ?? 'N/A' }}</p>
                    <p class="mt-2 text-sm text-muted-foreground">Company</p>
                    <p class="font-medium">{{ subscriber?.company ?? 'N/A' }}</p>
                    <p class="mt-2 text-sm text-muted-foreground">Global config keys</p>
                    <p class="font-medium">{{ globalConfigCount ?? 0 }}</p>
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-[1.05fr_0.95fr]">
                <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <p class="text-sm text-muted-foreground">How this request works</p>
                    <div class="mt-4 space-y-3">
                        <div
                            v-for="(step, index) in requestSteps"
                            :key="step.title"
                            class="flex gap-3 rounded-lg bg-muted/40 p-3"
                        >
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-foreground text-background">
                                {{ index + 1 }}
                            </div>
                            <div>
                                <p class="font-medium">{{ step.title }}</p>
                                <p class="text-sm text-muted-foreground">{{ step.detail }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <p class="text-sm text-muted-foreground">Data ownership</p>
                    <div class="mt-4 space-y-3">
                        <div
                            v-for="zone in dataZones"
                            :key="zone.label"
                            class="rounded-lg border p-3"
                            :class="zone.tone"
                        >
                            <p class="text-xs uppercase tracking-wide text-muted-foreground">{{ zone.label }}</p>
                            <p class="mt-1 font-medium">{{ zone.value }}</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <span
                                    v-for="item in zone.items"
                                    :key="item"
                                    class="rounded-full bg-background/90 px-3 py-1 text-xs font-medium"
                                >
                                    {{ item }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Employee section</p>
                        <p class="text-lg font-semibold">Manage tenant employees</p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Create and view employees stored inside this tenant database.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button as-child>
                            <Link :href="route('employees.create')">Create employee</Link>
                        </Button>
                        <Button variant="outline" as-child>
                            <Link :href="route('employees.index')">View all employees</Link>
                        </Button>
                    </div>
                </div>

                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    <div class="rounded-lg bg-muted/40 p-4">
                        <p class="text-xs uppercase tracking-wide text-muted-foreground">Total employees</p>
                        <p class="mt-2 text-2xl font-semibold">{{ employeeSummary?.total ?? 0 }}</p>
                    </div>
                    <div class="rounded-lg bg-muted/40 p-4">
                        <p class="text-xs uppercase tracking-wide text-muted-foreground">Active employees</p>
                        <p class="mt-2 text-2xl font-semibold text-emerald-600">{{ employeeSummary?.active ?? 0 }}</p>
                    </div>
                    <div class="rounded-lg bg-muted/40 p-4">
                        <p class="text-xs uppercase tracking-wide text-muted-foreground">Stored in</p>
                        <p class="mt-2 text-sm font-mono">{{ tenant?.database ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="mt-4 rounded-lg border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="border-b border-sidebar-border/70 px-4 py-3 text-sm font-medium dark:border-sidebar-border">
                        Recent employees
                    </div>

                    <div v-if="employeeSummary?.recent?.length" class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                        <div v-for="employee in employeeSummary.recent" :key="employee.id" class="flex flex-col gap-2 px-4 py-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="font-medium">{{ employee.name }}</p>
                                <p class="text-sm text-muted-foreground">{{ employee.email }}</p>
                            </div>
                            <div class="text-sm text-muted-foreground">
                                {{ employee.designation || 'No designation' }} / {{ employee.department || 'No department' }}
                            </div>
                            <span
                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                :class="employee.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700'"
                            >
                                {{ employee.status }}
                            </span>
                        </div>
                    </div>

                    <div v-else class="px-4 py-8 text-center text-sm text-muted-foreground">
                        No employees created yet. Use the Create employee button above.
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                <p class="text-sm text-muted-foreground">Database-per-tenant architecture</p>
                <p class="mt-2 text-sm">
                    This workspace runs against the isolated tenant database
                    <span class="font-mono">{{ tenant?.database ?? 'N/A' }}</span>
                    while plans, subscriptions, subscribers, and global settings remain in the landlord database.
                </p>
                <div class="mt-4 grid gap-2 md:grid-cols-2">
                    <div class="rounded-lg bg-muted/40 p-3">
                        <p class="text-xs uppercase tracking-wide text-muted-foreground">Tenant Domain</p>
                        <p class="font-medium">{{ tenant?.domain ?? 'N/A' }}</p>
                    </div>
                    <div class="rounded-lg bg-muted/40 p-3">
                        <p class="text-xs uppercase tracking-wide text-muted-foreground">Tenant Slug</p>
                        <p class="font-medium">{{ tenant?.slug ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                <p class="text-sm text-muted-foreground">Plan Features</p>
                <div v-if="plan && Object.keys(plan.features).length" class="mt-3 grid gap-2 md:grid-cols-2">
                    <div v-for="(enabled, feature) in plan.features" :key="feature" class="rounded-lg bg-muted/40 p-3">
                        <p class="font-medium">{{ feature }}</p>
                        <p class="text-sm text-muted-foreground">{{ enabled ? 'Enabled' : 'Disabled' }}</p>
                    </div>
                </div>
                <p v-else class="mt-3 text-sm text-muted-foreground">No plan features configured yet.</p>
            </div>
        </div>
    </AppLayout>
</template>
