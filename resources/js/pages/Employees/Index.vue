<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { computed } from 'vue';

interface Employee {
    id: number;
    name: string;
    email: string;
    phone?: string | null;
    designation?: string | null;
    department?: string | null;
    status: string;
    hired_at?: string | null;
    notes?: string | null;
    created_at?: string | null;
}

const props = defineProps<{
    employees: Employee[];
    status?: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Employees',
        href: '/employees',
    },
];

const totals = computed(() => ({
    all: props.employees.length,
    active: props.employees.filter((employee) => employee.status === 'active').length,
    inactive: props.employees.filter((employee) => employee.status === 'inactive').length,
}));

const removeEmployee = (employee: Employee) => {
    if (!window.confirm(`Delete employee "${employee.name}"?`)) {
        return;
    }

    router.delete(route('employees.destroy', employee.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Employees" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex flex-col gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">Tenant employee management</p>
                    <h1 class="text-2xl font-semibold tracking-tight">Employees</h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Add and manage employees inside the active tenant database.
                    </p>
                </div>

                <Button as-child>
                    <Link :href="route('employees.create')">Add employee</Link>
                </Button>
            </div>

            <div v-if="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ status }}
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <p class="text-sm text-muted-foreground">Total employees</p>
                    <p class="mt-2 text-3xl font-semibold">{{ totals.all }}</p>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <p class="text-sm text-muted-foreground">Active employees</p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-600">{{ totals.active }}</p>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <p class="text-sm text-muted-foreground">Inactive employees</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-500">{{ totals.inactive }}</p>
                </div>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <div class="border-b border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                    <p class="text-sm font-medium">Employee list</p>
                </div>

                <div v-if="employees.length" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-sidebar-border/70 text-sm dark:divide-sidebar-border">
                        <thead class="bg-muted/40 text-left">
                            <tr>
                                <th class="px-4 py-3 font-medium">Name</th>
                                <th class="px-4 py-3 font-medium">Contact</th>
                                <th class="px-4 py-3 font-medium">Role</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 font-medium">Hired</th>
                                <th class="px-4 py-3 font-medium text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                            <tr v-for="employee in employees" :key="employee.id">
                                <td class="px-4 py-4 align-top">
                                    <p class="font-medium">{{ employee.name }}</p>
                                    <p v-if="employee.notes" class="mt-1 max-w-md text-xs text-muted-foreground">
                                        {{ employee.notes }}
                                    </p>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <p>{{ employee.email }}</p>
                                    <p class="text-xs text-muted-foreground">{{ employee.phone || 'No phone' }}</p>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <p>{{ employee.designation || 'No designation' }}</p>
                                    <p class="text-xs text-muted-foreground">{{ employee.department || 'No department' }}</p>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                        :class="
                                            employee.status === 'active'
                                                ? 'bg-emerald-100 text-emerald-700'
                                                : 'bg-slate-200 text-slate-700'
                                        "
                                    >
                                        {{ employee.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    {{ employee.hired_at || 'Not set' }}
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="outline" as-child>
                                            <Link :href="route('employees.edit', employee.id)">Edit</Link>
                                        </Button>
                                        <Button variant="destructive" @click="removeEmployee(employee)">Delete</Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="px-4 py-10 text-center">
                    <p class="text-base font-medium">No employees yet</p>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Create your first employee record in this tenant workspace.
                    </p>
                    <Button class="mt-4" as-child>
                        <Link :href="route('employees.create')">Create employee</Link>
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
