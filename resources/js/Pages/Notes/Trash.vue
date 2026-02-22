<template>
    <AppLayout>
        <div class="px-6 py-4 border-b border-gray-200 bg-white sticky top-0">
            <h2 class="text-lg font-semibold text-gray-800">Papierkorb</h2>
        </div>

        <div class="divide-y divide-gray-100">
            <div v-if="notes.data.length === 0" class="flex flex-col items-center justify-center py-24 text-gray-400">
                <p class="text-sm">Papierkorb ist leer</p>
            </div>

            <div v-for="note in notes.data" :key="note.id" class="flex items-center gap-4 px-6 py-4">
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-600 truncate">{{ note.title }}</p>
                    <p class="text-xs text-gray-400">Gelöscht: {{ formatDate(note.deleted_at) }}</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button
                        @click="restore(note.id)"
                        class="px-3 py-1 text-sm text-indigo-600 border border-indigo-200 rounded-lg hover:bg-indigo-50"
                    >Wiederherstellen</button>
                    <button
                        @click="forceDelete(note.id)"
                        class="px-3 py-1 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50"
                    >Endgültig löschen</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({ notes: Object });

function restore(id) {
    router.patch(route('notes.restore', id));
}

function forceDelete(id) {
    if (confirm('Notiz endgültig und unwiderruflich löschen?')) {
        router.delete(route('notes.force-delete', id));
    }
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('de-DE', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>
