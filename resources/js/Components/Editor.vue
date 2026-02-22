<template>
    <div class="editor-wrapper">
        <div v-if="editor" class="editor-toolbar border-b border-gray-200 px-2 py-1 flex flex-wrap gap-1">
            <button
                v-for="btn in toolbarButtons"
                :key="btn.label"
                type="button"
                @click="btn.action()"
                :class="['toolbar-btn', btn.active?.() ? 'active' : '']"
                :title="btn.label"
            >
                <span v-html="btn.icon"></span>
            </button>
        </div>
        <editor-content :editor="editor" class="editor-content" />
    </div>
</template>

<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import TextAlign from '@tiptap/extension-text-align';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import { watch } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Notiz schreiben...' },
});

const emit = defineEmits(['update:modelValue', 'update:json']);

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit,
        Underline,
        TextAlign.configure({ types: ['heading', 'paragraph'] }),
        Link.configure({ openOnClick: false }),
        Placeholder.configure({ placeholder: props.placeholder }),
    ],
    onUpdate({ editor }) {
        emit('update:modelValue', editor.getHTML());
        emit('update:json', editor.getJSON());
    },
    editorProps: {
        attributes: { class: 'prose prose-sm max-w-none focus:outline-none min-h-64 p-4' },
    },
});

watch(() => props.modelValue, (val) => {
    if (editor.value && editor.value.getHTML() !== val) {
        editor.value.commands.setContent(val, false);
    }
});

const toolbarButtons = [
    { label: 'Fett',         icon: '<b>B</b>',  action: () => editor.value.chain().focus().toggleBold().run(),        active: () => editor.value?.isActive('bold') },
    { label: 'Kursiv',       icon: '<i>I</i>',  action: () => editor.value.chain().focus().toggleItalic().run(),      active: () => editor.value?.isActive('italic') },
    { label: 'Unterstrichen',icon: '<u>U</u>',  action: () => editor.value.chain().focus().toggleUnderline().run(),   active: () => editor.value?.isActive('underline') },
    { label: 'Durchgestrichen', icon: '<s>S</s>', action: () => editor.value.chain().focus().toggleStrike().run(),    active: () => editor.value?.isActive('strike') },
    { label: '|', icon: '<span class="text-gray-300">|</span>', action: () => {} },
    { label: 'H1',           icon: 'H1',        action: () => editor.value.chain().focus().toggleHeading({ level: 1 }).run(), active: () => editor.value?.isActive('heading', { level: 1 }) },
    { label: 'H2',           icon: 'H2',        action: () => editor.value.chain().focus().toggleHeading({ level: 2 }).run(), active: () => editor.value?.isActive('heading', { level: 2 }) },
    { label: '|', icon: '<span class="text-gray-300">|</span>', action: () => {} },
    { label: 'Liste',        icon: '&#8226;&#8212;', action: () => editor.value.chain().focus().toggleBulletList().run(),  active: () => editor.value?.isActive('bulletList') },
    { label: 'Num. Liste',   icon: '1.&#8212;',      action: () => editor.value.chain().focus().toggleOrderedList().run(), active: () => editor.value?.isActive('orderedList') },
    { label: 'Blockquote',   icon: '&#10077;',  action: () => editor.value.chain().focus().toggleBlockquote().run(),   active: () => editor.value?.isActive('blockquote') },
    { label: 'Code',         icon: '&lt;/&gt;', action: () => editor.value.chain().focus().toggleCodeBlock().run(),    active: () => editor.value?.isActive('codeBlock') },
];
</script>

<style scoped>
.toolbar-btn {
    @apply px-2 py-1 text-xs rounded text-gray-600 hover:bg-gray-100 font-mono transition-colors;
}
.toolbar-btn.active {
    @apply bg-indigo-100 text-indigo-700;
}
</style>
