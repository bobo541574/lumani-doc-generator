<script setup lang="ts">
import { ref, onMounted } from 'vue';

const stats = ref({
    projects: 0,
    documents: 0,
    recentActivity: [] as Array<{ id: string; action: string; created_at: string }>,
});

const loading = ref(true);

function pageTenant(): string | null {
    const el = document.getElementById('app') as HTMLElement | null;
    if (!el || !el.dataset.page) return null;
    return JSON.parse(el.dataset.page).tenant ?? null;
}

onMounted(async () => {
    const tenant = pageTenant();
    if (!tenant) {
        loading.value = false;
        return;
    }

    try {
        const response = await fetch('/api/v1/dashboard/stats', {
            headers: { 'X-Tenant-ID': tenant },
        });
        if (response.ok) {
            stats.value = await response.json();
        }
    } catch (error) {
        console.error('Failed to load dashboard stats:', error);
    } finally {
        loading.value = false;
    }
});
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <h1 class="text-2xl font-bold text-gray-900">
          Lumani Documentation Platform
        </h1>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div
        v-if="loading"
        class="flex items-center justify-center h-64"
      >
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600" />
      </div>

      <div
        v-else
        class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8"
      >
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-sm font-medium text-gray-500">
            Projects
          </h3>
          <p class="mt-2 text-3xl font-bold text-gray-900">
            {{ stats.projects }}
          </p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-sm font-medium text-gray-500">
            Documents
          </h3>
          <p class="mt-2 text-3xl font-bold text-gray-900">
            {{ stats.documents }}
          </p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-sm font-medium text-gray-500">
            Recent Activity
          </h3>
          <p class="mt-2 text-3xl font-bold text-gray-900">
            {{ stats.recentActivity.length }}
          </p>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
          Recent Activity
        </h2>
        <div
          v-if="stats.recentActivity.length === 0"
          class="text-gray-500 text-center py-8"
        >
          No recent activity
        </div>
        <ul
          v-else
          class="divide-y divide-gray-200"
        >
          <li
            v-for="activity in stats.recentActivity"
            :key="activity.id"
            class="py-3"
          >
            <span class="text-sm text-gray-900">{{ activity.action }}</span>
            <span class="text-xs text-gray-500 ml-2">{{ activity.created_at }}</span>
          </li>
        </ul>
      </div>
    </main>
  </div>
</template>
