<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { Head, Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const auth = page.props.auth as {
    user?: {
        name: string;
        email: string;
    } | null;
    guard?: 'tenant' | 'landlord' | null;
};

defineProps<{
    title: string;
    description: string;
}>();
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen bg-[linear-gradient(180deg,#fffaf2_0%,#f5efe3_52%,#efe8da_100%)] text-slate-900">
        <div class="mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-8 lg:px-10">
            <header class="flex flex-col gap-4 rounded-[28px] border border-slate-900/10 bg-white/80 px-5 py-4 shadow-[0_20px_80px_rgba(39,29,9,0.08)] backdrop-blur md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-900 text-amber-100">
                        <AppLogoIcon className="size-6" />
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-700">Landlord Control Plane</p>
                        <p class="text-sm text-slate-600">Custom database-per-tenant architecture</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <Button as-child variant="outline" class="border-slate-300 bg-white/80">
                        <Link :href="route('login')">Tenant Login</Link>
                    </Button>
                    <Button v-if="auth.guard === 'landlord'" as-child variant="outline" class="border-slate-300 bg-white/80">
                        <Link :href="route('landlord.logout')" method="post" as="button">Log out</Link>
                    </Button>
                    <Button v-else as-child class="bg-slate-900 text-white hover:bg-slate-800">
                        <Link :href="route('landlord.login')">Landlord Login</Link>
                    </Button>
                </div>
            </header>

            <main class="flex-1 py-8">
                <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
                    <section class="rounded-[32px] border border-slate-900/10 bg-slate-950 px-6 py-8 text-white shadow-[0_24px_90px_rgba(15,23,42,0.22)] lg:px-8">
                        <p class="text-xs font-semibold uppercase tracking-[0.34em] text-amber-300">Overview</p>
                        <h1 class="mt-4 max-w-3xl text-4xl font-semibold tracking-tight text-white md:text-5xl">
                            {{ title }}
                        </h1>
                        <p class="mt-4 max-w-2xl text-base leading-7 text-slate-300">
                            {{ description }}
                        </p>
                        <slot name="hero" />
                    </section>

                    <aside class="rounded-[32px] border border-slate-900/10 bg-white/85 p-6 shadow-[0_24px_90px_rgba(120,90,26,0.12)]">
                        <slot name="aside" />
                    </aside>
                </div>

                <div class="mt-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
