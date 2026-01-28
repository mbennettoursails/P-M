/**
 * Tiptap Editor Alpine.js Component
 * 
 * This component integrates Tiptap with Alpine.js and Livewire
 * for a seamless rich text editing experience.
 */

import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Underline from '@tiptap/extension-underline'
import Link from '@tiptap/extension-link'
import TextAlign from '@tiptap/extension-text-align'
import Placeholder from '@tiptap/extension-placeholder'
import Image from '@tiptap/extension-image'

document.addEventListener('alpine:init', () => {
    Alpine.data('tiptapEditor', (config) => ({
        editor: null,
        content: config.content || '',
        contentJson: config.contentJson || '',
        editorId: config.editorId,
        placeholder: config.placeholder || '',
        autofocus: config.autofocus || false,
        minHeight: config.minHeight || '200px',
        showToolbar: config.showToolbar !== false,
        enabledExtensions: config.enabledExtensions || [],

        initEditor() {
            const editorElement = document.getElementById(this.editorId);
            if (!editorElement) {
                console.error('Tiptap: Editor element not found:', this.editorId);
                return;
            }

            // Build extensions array based on enabled extensions
            const extensions = [
                StarterKit.configure({
                    heading: {
                        levels: [2, 3, 4],
                    },
                }),
            ];

            // Add optional extensions
            if (this.enabledExtensions.includes('underline')) {
                extensions.push(Underline);
            }

            if (this.enabledExtensions.includes('link')) {
                extensions.push(
                    Link.configure({
                        openOnClick: false,
                        HTMLAttributes: {
                            class: 'text-green-600 hover:text-green-700 underline',
                        },
                    })
                );
            }

            if (this.enabledExtensions.includes('textAlign')) {
                extensions.push(
                    TextAlign.configure({
                        types: ['heading', 'paragraph'],
                    })
                );
            }

            if (this.enabledExtensions.includes('image')) {
                extensions.push(
                    Image.configure({
                        HTMLAttributes: {
                            class: 'rounded-lg max-w-full',
                        },
                    })
                );
            }

            if (this.placeholder) {
                extensions.push(
                    Placeholder.configure({
                        placeholder: this.placeholder,
                    })
                );
            }

            // Initialize editor
            this.editor = new Editor({
                element: editorElement,
                extensions: extensions,
                content: this.content,
                autofocus: this.autofocus,
                editorProps: {
                    attributes: {
                        class: 'prose prose-sm dark:prose-invert max-w-none focus:outline-none min-h-[inherit]',
                    },
                },
                onUpdate: ({ editor }) => {
                    this.content = editor.getHTML();
                    this.contentJson = JSON.stringify(editor.getJSON());
                    
                    // Dispatch event for Livewire
                    this.$dispatch('tiptap-updated', {
                        html: this.content,
                        json: this.contentJson
                    });
                },
            });

            // Watch for external content changes (e.g., from Livewire)
            this.$watch('content', (newContent) => {
                if (this.editor && newContent !== this.editor.getHTML()) {
                    this.editor.commands.setContent(newContent, false);
                }
            });
        },

        // Toolbar actions
        toggleBold() {
            this.editor?.chain().focus().toggleBold().run();
        },

        toggleItalic() {
            this.editor?.chain().focus().toggleItalic().run();
        },

        toggleUnderline() {
            this.editor?.chain().focus().toggleUnderline().run();
        },

        toggleStrike() {
            this.editor?.chain().focus().toggleStrike().run();
        },

        toggleHeading(level) {
            this.editor?.chain().focus().toggleHeading({ level }).run();
        },

        toggleBulletList() {
            this.editor?.chain().focus().toggleBulletList().run();
        },

        toggleOrderedList() {
            this.editor?.chain().focus().toggleOrderedList().run();
        },

        toggleBlockquote() {
            this.editor?.chain().focus().toggleBlockquote().run();
        },

        toggleCodeBlock() {
            this.editor?.chain().focus().toggleCodeBlock().run();
        },

        setHorizontalRule() {
            this.editor?.chain().focus().setHorizontalRule().run();
        },

        setLink() {
            const previousUrl = this.editor?.getAttributes('link').href;
            const url = window.prompt('URL:', previousUrl);

            if (url === null) {
                return;
            }

            if (url === '') {
                this.editor?.chain().focus().extendMarkRange('link').unsetLink().run();
                return;
            }

            this.editor?.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
        },

        setTextAlign(alignment) {
            this.editor?.chain().focus().setTextAlign(alignment).run();
        },

        addImage() {
            const url = window.prompt('Image URL:');
            if (url) {
                this.editor?.chain().focus().setImage({ src: url }).run();
            }
        },

        undo() {
            this.editor?.chain().focus().undo().run();
        },

        redo() {
            this.editor?.chain().focus().redo().run();
        },

        // Check if a format is active
        isActive(type, attributes = {}) {
            if (!this.editor) return false;
            
            if (typeof type === 'object') {
                // For textAlign checks
                return this.editor.isActive(type);
            }
            
            return this.editor.isActive(type, attributes);
        },

        // Cleanup
        destroy() {
            this.editor?.destroy();
        }
    }));
});

// Export for module usage
export { Editor, StarterKit, Underline, Link, TextAlign, Placeholder, Image };
