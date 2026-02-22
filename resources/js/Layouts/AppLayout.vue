<template>
    <div class="flex h-screen bg-gray-50 overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col shrink-0">
            <!-- Logo -->
            <div class="px-4 py-4 border-b border-gray-200">
                <h1 class="text-xl font-bold text-indigo-600">Elma Note</h1>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <Link
                    :href="route('notes.index')"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100"
                    :class="{ 'bg-indigo-50 text-indigo-700': $page.url.startsWith('/notes') && !$page.url.includes('trash') }"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Alle Notizen
                </Link>

                <Link
                    :href="route('notes.index', { favorites: true })"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    Favoriten
                </Link>

                <!-- Notebooks -->
                <div class="pt-3">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Notizbücher</p>
                    <Link
                        v-for="nb in notebooks"
                        :key="nb.id"
                        :href="route('notes.index', { notebook: nb.id })"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm text-gray-700 hover:bg-gray-100"
                    >
                        <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: nb.color }"></span>
                        <span class="truncate">{{ nb.name }}</span>
                        <span class="ml-auto text-xs text-gray-400">{{ nb.notes_count }}</span>
                    </Link>
                </div>

                <!-- Tags -->
                <div class="pt-3" v-if="tags.length">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Tags</p>
                    <Link
                        v-for="tag in tags"
                        :key="tag.id"
                        :href="route('notes.index', { tag: tag.id })"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm text-gray-700 hover:bg-gray-100"
                    >
                        <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: tag.color }"></span>
                        <span class="truncate">{{ tag.name }}</span>
                        <span class="ml-auto text-xs text-gray-400">{{ tag.notes_count }}</span>
                    </Link>
                </div>

                <Link
                    :href="route('notes.trash')"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-500 hover:bg-gray-100 mt-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Papierkorb
                </Link>
            </nav>

            <!-- User -->
            <div class="px-4 py-3 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 truncate">{{ $page.props.auth.user.name }}</span>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-xs text-gray-400 hover:text-gray-600"
                    >Abmelden</Link>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 overflow-y-auto">
            <!-- Flash message -->
            <div
                v-if="$page.props.flash?.success"
                class="m-4 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800"
            >
                {{ $page.props.flash.success }}
            </div>

            <slot />
        </main>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    notebooks: { type: Array, default: () => [] },
    tags:      { type: Array, default: () => [] },
});
</script>
