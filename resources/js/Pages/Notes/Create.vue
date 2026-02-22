<template>
    <AppLayout :notebooks="notebooks" :tags="tags">
        <div class="max-w-4xl mx-auto px-6 py-6">
            <div class="flex items-center justify-between mb-4">
                <Link :href="route('notes.index')" class="text-sm text-gray-500 hover:text-gray-700">← Zurück</Link>
                <button @click="submit" class="btn-primary" :disabled="form.processing">Erstellen</button>
            </div>

            <input
                v-model="form.title"
                class="w-full text-2xl font-bold text-gray-900 border-none outline-none bg-transparent mb-4 placeholder-gray-300"
                placeholder="Titel..."
                autofocus
            />

            <div class="flex flex-wrap items-center gap-3 mb-4 pb-4 border-b border-gray-200">
                <select v-model="form.notebook_id" class="text-sm border border-gray-200 rounded-lg px-2 py-1 text-gray-600">
                    <option :value="null">Kein Notizbuch</option>
                    <option v-for="nb in notebooks" :key="nb.id" :value="nb.id">{{ nb.name }}</option>
                </select>
                <span
                    v-for="tag in tags" :key="tag.id"
                    @click="toggleTag(tag.id)"
                    class="px-2 py-0.5 rounded-full text-xs cursor-pointer border transition-colors"
                    :class="form.tags.includes(tag.id) ? 'border-transparent text-white' : 'border-gray-200 text-gray-500 hover:border-gray-400'"
                    :style="form.tags.includes(tag.id) ? { backgroundColor: tag.color } : {}"
                >{{ tag.name }}</span>
            </div>

            <Editor
                v-model="form.content"
                @update:json="form.content_json = $event"
                placeholder="Notiz schreiben..."
                class="bg-white rounded-xl border border-gray-200 overflow-hidden"
            />

            <p v-if="form.errors.title" class="mt-2 text-sm text-red-600">{{ form.errors.title }}</p>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Editor from '@/Components/Editor.vue';

const props = defineProps({ notebooks: Array, tags: Array });

const form = useForm({
    title:        '',
    content:      '',
    content_json: null,
    notebook_id:  null,
    tags:         [],
    is_favorite:  false,
    is_pinned:    false,
});

function toggleTag(id) {
    const idx = form.tags.indexOf(id);
    if (idx === -1) form.tags.push(id);
    else form.tags.splice(idx, 1);
}

function submit() {
    form.post(route('notes.store'));
}
</script>

<style scoped>
.btn-primary { @apply px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50; }
</style>
