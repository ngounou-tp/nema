<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import {
    PhChartLineUp,
    PhChatCircleText,
    PhFirstAidKit,
    PhGauge,
    PhQrCode,
    PhStorefront,
    PhUsersThree,
} from '@phosphor-icons/vue';
import { computed } from 'vue';
import DemoBanner from '@/components/DemoBanner.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import { Button } from '@/components/ui/button';
import { login, register } from '@/routes';
import demo from '@/routes/demo';
import type { UserRole } from '@/types';

const page = usePage();
const demoMode = computed(() => page.props.demoMode);

/** Order of the jury flow: the producer's diagnosis comes first. */
const demoRoles: UserRole[] = ['producer', 'cooperative', 'buyer', 'admin'];

const modules = [
    { key: 'diagnosis', icon: PhFirstAidKit },
    { key: 'assistant', icon: PhChatCircleText },
    { key: 'marketplace', icon: PhStorefront },
    { key: 'score', icon: PhGauge },
    { key: 'traceability', icon: PhQrCode },
    { key: 'forecasts', icon: PhChartLineUp },
    { key: 'cooperative', icon: PhUsersThree },
] as const;
</script>

<template>
    <Head :title="$t('home.meta_title')" />

    <div class="flex min-h-svh flex-col">
        <DemoBanner />
        <PublicHeader />

        <main class="mx-auto w-full max-w-6xl flex-1 px-4 md:px-8">
            <div class="rule-double" aria-hidden="true" />

            <section
                class="grid gap-10 py-10 md:grid-cols-[minmax(0,7fr)_minmax(0,5fr)] md:gap-16 md:py-16"
                aria-labelledby="home-title"
            >
                <div>
                    <h1
                        id="home-title"
                        class="text-[2.5rem] leading-[1.05] tracking-[-0.02em] md:text-6xl"
                    >
                        {{ $t('home.title') }}
                    </h1>
                    <p
                        class="mt-5 max-w-[34em] text-lg leading-relaxed md:text-xl"
                    >
                        {{ $t('home.lead') }}
                    </p>
                </div>

                <!-- The jury's entry point: one click per role, no account to create. -->
                <div
                    class="self-end rounded-md bg-card p-5 shadow-sm md:p-6"
                    aria-labelledby="try-as-title"
                    role="group"
                >
                    <template v-if="demoMode">
                        <h2 id="try-as-title" class="text-xl">
                            {{ $t('home.try_as.title') }}
                        </h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ $t('home.try_as.description') }}
                        </p>
                        <ul class="mt-4 grid gap-2">
                            <li v-for="role in demoRoles" :key="role">
                                <Form
                                    v-bind="demo.login.form(role)"
                                    #default="{ processing }"
                                >
                                    <Button
                                        type="submit"
                                        :variant="
                                            role === 'producer'
                                                ? 'default'
                                                : 'outline'
                                        "
                                        size="lg"
                                        class="h-auto w-full flex-col items-start gap-0 py-2.5 text-left whitespace-normal"
                                        :disabled="processing"
                                        :data-test="`demo-login-${role}`"
                                    >
                                        <span>{{
                                            $t(`enums.user_role.${role}`)
                                        }}</span>
                                        <span
                                            class="text-sm font-normal opacity-85"
                                            >{{
                                                $t(`home.try_as.roles.${role}`)
                                            }}</span
                                        >
                                    </Button>
                                </Form>
                            </li>
                        </ul>
                    </template>

                    <template v-else>
                        <h2 id="try-as-title" class="text-xl">
                            {{ $t('home.join.title') }}
                        </h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ $t('home.join.description') }}
                        </p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <Button as-child size="lg">
                                <Link :href="register()">{{
                                    $t('nav.register')
                                }}</Link>
                            </Button>
                            <Button as-child size="lg" variant="outline">
                                <Link :href="login()">{{
                                    $t('nav.login')
                                }}</Link>
                            </Button>
                        </div>
                    </template>
                </div>
            </section>

            <section
                class="border-t border-ink py-10 md:py-14"
                aria-labelledby="modules-title"
            >
                <h2 id="modules-title" class="text-2xl md:text-3xl">
                    {{ $t('home.modules_title') }}
                </h2>
                <ul
                    class="mt-6 grid gap-x-10 gap-y-7 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <li
                        v-for="module in modules"
                        :key="module.key"
                        class="flex gap-3"
                    >
                        <component
                            :is="module.icon"
                            weight="duotone"
                            class="mt-0.5 size-7 shrink-0 text-brand-700"
                            aria-hidden="true"
                        />
                        <div>
                            <h3 class="text-lg">
                                {{ $t(`modules.${module.key}.title`) }}
                            </h3>
                            <p class="mt-1 text-[15px] text-neutral-800">
                                {{ $t(`modules.${module.key}.description`) }}
                            </p>
                        </div>
                    </li>
                </ul>
            </section>
        </main>

        <footer class="border-t border-border">
            <p
                class="mx-auto w-full max-w-6xl px-4 py-5 text-sm text-muted-foreground md:px-8"
            >
                {{ $t('home.footer') }}
            </p>
        </footer>
    </div>
</template>
