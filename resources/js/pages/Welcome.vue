<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>

    <Head title="Welcome | Nilufar Music Bot">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        <header class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl">
            <nav class="flex items-center justify-end gap-4">
                <Link v-if="$page.props.auth.user" :href="dashboard()" class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]">
                    Dashboard
                </Link>
                <template v-else>
                    <Link :href="login()" class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]">
                        Log in
                    </Link>
                    <Link v-if="canRegister" :href="register()" class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]">
                        Register
                    </Link>
                </template>
            </nav>
        </header>

        <main class="flex w-full max-w-[335px] flex-col overflow-hidden rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:max-w-4xl lg:p-20 dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
            <div class="text-center">
                <h1 class="text-3xl font-bold tracking-tight text-[#1b1b18] dark:text-[#EDEDEC] sm:text-4xl">
                    Nilufar Music Bot 🎵
                </h1>
                <p class="mt-4 text-lg leading-8 text-[#706f6c] dark:text-[#A1A09A]">
                    Your ultimate Telegram companion for searching, downloading, and listening to music. Connect your accounts for smart recommendations.
                </p>

                <div class="mt-10 flex items-center justify-center gap-x-6">
                    <a href="https://t.me/nilufar_the_bot" target="_blank" class="rounded-md bg-[#1b1b18] px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#333330] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1b1b18] dark:bg-[#EDEDEC] dark:text-[#161615] dark:hover:bg-[#d4d4d2]">
                        Start Bot on Telegram
                    </a>
                </div>
            </div>

            <div class="mt-16 flow-root sm:mt-24">
                <div class="-m-2 rounded-xl bg-gray-900/5 p-2 ring-1 ring-inset ring-gray-900/10 lg:-m-4 lg:rounded-2xl lg:p-4 dark:bg-white/5 dark:ring-white/10">
                    <div class="rounded-md bg-white p-6 shadow-2xl ring-1 ring-gray-900/10 dark:bg-[#0a0a0a] dark:ring-white/10">
                        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                            <!-- Feature 1 -->
                            <div class="flex flex-col gap-2">
                                <span class="text-xl font-semibold">🔍 Search & Download</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Find any song instantly and get high-quality audio files directly in your chat.</p>
                            </div>
                            <!-- Feature 2 -->
                            <div class="flex flex-col gap-2">
                                <span class="text-xl font-semibold">▶️ Queue System</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Build your perfect playlist on the fly. Add tracks and play them seamlessly.</p>
                            </div>
                            <!-- Feature 3 -->
                            <div class="flex flex-col gap-2">
                                <span class="text-xl font-semibold">🔗 Smart Sync</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Connect Google & Yandex accounts to sync your listening history for better recommendations.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="mt-16 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
            &copy; {{ new Date().getFullYear() }} Nilufar Music Bot. All rights reserved.
        </footer>
    </div>
</template>
