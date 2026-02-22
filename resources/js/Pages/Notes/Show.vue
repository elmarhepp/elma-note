<template>
    <AppLayout :notebooks="notebooks" :tags="tags">
        <div class="max-w-4xl mx-auto px-6 py-6">
            <!-- Toolbar -->
            <div class="flex items-center justify-between mb-4">
                <Link :href="route('notes.index')" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
                    ← Zurück
                </Link>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400 transition-opacity" :class="saveStatus === 'idle' ? 'opacity-0' : 'opacity-100'">
                        <span v-if="saveStatus === 'saving'" class="text-gray-400">Speichern...</span>
                        <span v-if="saveStatus === 'saved'" class="text-green-600">Gespeichert ✓</span>
                    </span>
                    <button @click="toggleFavorite" class="icon-btn" :class="{ 'text-amber-400': form.is_favorite }" title="Favorit">★</button>
                    <button @click="togglePin"      class="icon-btn" :class="{ 'text-indigo-500': form.is_pinned }"  title="Anheften">📌</button>
                    <button @click="saveNote"       class="btn-primary" :disabled="form.processing">Speichern</button>
                    <button @click="deleteNote"     class="btn-danger">Löschen</button>
                </div>
            </div>

            <!-- Title -->
            <input
                v-model="form.title"
                class="w-full text-2xl font-bold text-gray-900 border-none outline-none bg-transparent mb-4 placeholder-gray-300"
                placeholder="Ohne Titel"
            />

            <!-- Meta -->
            <div class="flex flex-wrap items-center gap-3 mb-4 pb-4 border-b border-gray-200">
                <!-- Notebook selector -->
                <select
                    v-model="form.notebook_id"
                    class="text-sm border border-gray-200 rounded-lg px-2 py-1 text-gray-600"
                >
                    <option :value="null">Kein Notizbuch</option>
                    <option v-for="nb in notebooks" :key="nb.id" :value="nb.id">{{ nb.name }}</option>
                </select>

                <!-- Tags -->
                <div class="flex flex-wrap gap-1">
                    <span
                        v-for="tag in allTags"
                        :key="tag.id"
                        @click="toggleTag(tag.id)"
                        class="px-2 py-0.5 rounded-full text-xs cursor-pointer border transition-colors"
                        :class="selectedTagIds.includes(tag.id)
                            ? 'border-transparent text-white'
                            : 'border-gray-200 text-gray-500 hover:border-gray-400'"
                        :style="selectedTagIds.includes(tag.id) ? { backgroundColor: tag.color || '#6366f1' } : {}"
                    >
                        {{ tag.name }}
                    </span>
                </div>

                <span class="text-xs text-gray-400 ml-auto">
                    Zuletzt geändert: {{ formatDate(note.updated_at) }}
                </span>
            </div>

            <!-- Editor -->
            <Editor
                v-model="form.content"
                @update:json="form.content_json = $event"
                class="bg-white rounded-xl border border-gray-200 overflow-hidden"
            />
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Editor from '@/Components/Editor.vue';

const props = defineProps({
    note:      Object,
    notebooks: Array,
    tags:      Array,
});

const allTags = computed(() => props.tags);

const form = useForm({
    title:        props.note.title,
    content:      props.note.content ?? '',
    content_json: props.note.content_json ?? null,
    notebook_id:  props.note.notebook_id,
    tags:         props.note.tags.map(t => t.id),
    is_favorite:  props.note.is_favorite,
    is_pinned:    props.note.is_pinned,
});

const selectedTagIds = computed(() => form.tags);

function toggleTag(id) {
    const idx = form.tags.indexOf(id);
    if (idx === -1) form.tags.push(id);
    else form.tags.splice(idx, 1);
}

// Autosave
const saveStatus = ref('idle'); // 'idle' | 'saving' | 'saved'
let autosaveTimer = null;

function scheduleAutosave() {
    saveStatus.value = 'saving';
    clearTimeout(autosaveTimer);
    autosaveTimer = setTimeout(() => {
        form.patch(route('notes.update', props.note.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                saveStatus.value = 'saved';
                setTimeout(() => { saveStatus.value = 'idle'; }, 2000);
            },
            onError: () => { saveStatus.value = 'idle'; },
        });
    }, 1500);
}

watch(
    () => [form.title, form.content, form.notebook_id],
    () => scheduleAutosave(),
);

watch(
    () => form.tags,
    () => scheduleAutosave(),
    { deep: true },
);

function toggleFavorite() { form.is_favorite = !form.is_favorite; saveNote(); }
function togglePin()      { form.is_pinned   = !form.is_pinned;   saveNote(); }

function saveNote() {
    clearTimeout(autosaveTimer);
    saveStatus.value = 'saving';
    form.patch(route('notes.update', props.note.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            saveStatus.value = 'saved';
            setTimeout(() => { saveStatus.value = 'idle'; }, 2000);
        },
        onError: () => { saveStatus.value = 'idle'; },
    });
}

function deleteNote() {
    if (confirm('Notiz in den Papierkorb verschieben?')) {
        router.delete(route('notes.destroy', props.note.id));
    }
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('de-DE', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>

<style scoped>
.btn-primary { @apply px-3 py-1.5 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50; }
.btn-danger  { @apply px-3 py-1.5 text-sm font-medium bg-red-50 text-red-600 rounded-lg hover:bg-red-100; }
.icon-btn    { @apply text-lg text-gray-300 hover:text-gray-500 transition-colors; }
</style>
