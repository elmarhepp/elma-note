<template>
    <AppLayout :notebooks="notebooks" :tags="tags">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-white sticky top-0 z-10">
            <div class="flex items-center gap-3 flex-1">
                <h2 class="text-lg font-semibold text-gray-800">
                    {{ pageTitle }}
                </h2>
                <form @submit.prevent="search" class="flex-1 max-w-sm">
                    <input
                        v-model="searchTerm"
                        type="search"
                        placeholder="Suchen..."
                        class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        @keyup.enter="search"
                    />
                </form>
            </div>
            <Link :href="route('notes.create')" class="btn-primary">
                + Neue Notiz
            </Link>
        </div>

        <!-- Note list -->
        <div class="divide-y divide-gray-100">
            <div v-if="notes.data.length === 0" class="flex flex-col items-center justify-center py-24 text-gray-400">
                <svg class="w-12 h-12 mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-sm">Keine Notizen gefunden</p>
            </div>

            <Link
                v-for="note in notes.data"
                :key="note.id"
                :href="route('notes.show', note.id)"
                class="flex items-start gap-4 px-6 py-4 hover:bg-gray-50 transition-colors group"
            >
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span v-if="note.is_pinned" class="text-indigo-500" title="Angeheftet">📌</span>
                        <span v-if="note.is_favorite" class="text-amber-400" title="Favorit">★</span>
                        <h3 class="font-medium text-gray-900 truncate">{{ note.title }}</h3>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-400">
                        <span v-if="note.notebook">{{ note.notebook.name }}</span>
                        <span v-if="note.notebook && note.tags?.length">·</span>
                        <span v-for="tag in note.tags" :key="tag.id" class="inline-flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: tag.color }"></span>
                            {{ tag.name }}
                        </span>
                        <span class="ml-auto">{{ formatDate(note.updated_at) }}</span>
                    </div>
                </div>
            </Link>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    notes:     Object,
    notebooks: Array,
    tags:      Array,
    filters:   Object,
});

const searchTerm = ref(props.filters?.search ?? '');

const pageTitle = (() => {
    if (props.filters?.favorites) return 'Favoriten';
    if (props.filters?.search)    return `Suche: "${props.filters.search}"`;
    return 'Alle Notizen';
})();

function search() {
    router.get(route('notes.index'), { ...props.filters, search: searchTerm.value }, { preserveState: true });
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('de-DE', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>

<style scoped>
.btn-primary {
    @apply px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors;
}
</style>
