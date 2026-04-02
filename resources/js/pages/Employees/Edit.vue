<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

const props = defineProps<{
    employee: {
        id: number;
        name: string;
        email: string;
        phone?: string | null;
        designation?: string | null;
        department?: string | null;
        status: string;
        hired_at?: string | null;
        notes?: string | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Employees',
        href: '/employees',
    },
    {
        title: 'Edit',
        href: `/employees/${props.employee.id}/edit`,
    },
];

const form = useForm({
    name: props.employee.name,
    email: props.employee.email,
    phone: props.employee.phone ?? '',
    designation: props.employee.designation ?? '',
    department: props.employee.department ?? '',
    status: props.employee.status,
    hired_at: props.employee.hired_at ?? '',
    notes: props.employee.notes ?? '',
});

const submit = () => {
    form.put(route('employees.update', props.employee.id));
};
</script>

<template>
    <Head title="Edit Employee" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                <p class="text-sm text-muted-foreground">Tenant database employee record</p>
                <h1 class="text-2xl font-semibold tracking-tight">Edit employee</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Update the employee details stored in the current tenant workspace.
                </p>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                <form @submit.prevent="submit" class="grid gap-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" required placeholder="Employee name" />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <Input id="email" type="email" v-model="form.email" required placeholder="employee@example.com" />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="phone">Phone</Label>
                            <Input id="phone" v-model="form.phone" placeholder="9876543210" />
                            <InputError :message="form.errors.phone" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="designation">Designation</Label>
                            <Input id="designation" v-model="form.designation" placeholder="Sales Executive" />
                            <InputError :message="form.errors.designation" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="department">Department</Label>
                            <Input id="department" v-model="form.department" placeholder="Sales" />
                            <InputError :message="form.errors.department" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="status">Status</Label>
                            <select
                                id="status"
                                v-model="form.status"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background"
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <InputError :message="form.errors.status" />
                        </div>

                        <div class="grid gap-2 md:col-span-2">
                            <Label for="hired_at">Hire date</Label>
                            <Input id="hired_at" type="date" v-model="form.hired_at" />
                            <InputError :message="form.errors.hired_at" />
                        </div>

                        <div class="grid gap-2 md:col-span-2">
                            <Label for="notes">Notes</Label>
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="4"
                                class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background"
                                placeholder="Optional notes about this employee"
                            />
                            <InputError :message="form.errors.notes" />
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <Button :disabled="form.processing">Update employee</Button>
                        <Button variant="outline" as-child>
                            <Link :href="route('employees.index')">Back</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
