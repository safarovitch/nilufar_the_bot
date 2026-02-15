<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    stats: {
        users_count: number;
        played_tracks_count: number;
        queue_jobs: number;
    };
    logs: string[];
}>();
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Admin Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ stats.users_count }}</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Played Tracks</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ stats.played_tracks_count }}</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Jobs</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">{{ stats.queue_jobs }}</div>
                    </div>
                </div>

                <!-- Logs -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium mb-4">Recent System Logs</h3>
                        <div class="bg-gray-100 dark:bg-gray-900 rounded-md p-4 overflow-x-auto text-xs font-mono h-96 overflow-y-scroll border border-gray-200 dark:border-gray-700">
                           <div v-if="logs.length === 0" class="text-gray-500 italic">No logs found.</div>
                           <div v-for="(line, index) in logs" :key="index" class="whitespace-nowrap py-0.5">
                                {{ line }}
                           </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
