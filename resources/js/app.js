import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import { Jodit } from 'jodit';
import 'jodit/es2021/jodit.min.css';

window.Alpine = Alpine;
window.Sortable = Sortable;

document.addEventListener('alpine:init', () => {
    Alpine.data('bulkTable', () => ({
        selected: [],
        get allIds() {
            return [...this.$root.querySelectorAll('input[data-bulk-id]')].map((el) => String(el.value));
        },
        get allSelected() {
            return this.allIds.length > 0 && this.selected.length === this.allIds.length;
        },
        get indeterminate() {
            return this.selected.length > 0 && !this.allSelected;
        },
        toggleAll(checked) {
            this.selected = checked ? [...this.allIds] : [];
            this.syncChecks();
        },
        toggle(id, checked) {
            id = String(id);
            if (checked) {
                if (!this.selected.includes(id)) this.selected.push(id);
            } else {
                this.selected = this.selected.filter((x) => x !== id);
            }
            this.syncChecks();
        },
        syncChecks() {
            this.$root.querySelectorAll('input[data-bulk-id]').forEach((el) => {
                el.checked = this.selected.includes(String(el.value));
            });
            const selectAll = this.$refs.selectAll;
            if (selectAll) {
                selectAll.indeterminate = this.indeterminate;
                selectAll.checked = this.allSelected;
            }
        },
    }));

    Alpine.data('sortableMenu', (reorderUrl) => ({
        init() {
            const el = this.$refs.list;
            if (!el || !window.Sortable) return;
            Sortable.create(el, {
                handle: '[data-drag-handle]',
                animation: 180,
                ghostClass: 'opacity-40',
                onEnd: async () => {
                    const order = [...el.querySelectorAll('[data-item-id]')].map((n) => n.dataset.itemId);
                    await fetch(reorderUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                        },
                        body: JSON.stringify({ order }),
                    });
                },
            });
        },
    }));
});

// Initialise Jodit on any [data-jodit] textarea.
function initEditors(root = document) {
    root.querySelectorAll('textarea[data-jodit]').forEach((el) => {
        if (el.dataset.joditReady) return;
        el.dataset.joditReady = '1';
        Jodit.make(el, {
            height: 420,
            uploader: {
                url: el.dataset.uploadUrl || '/admin/media/jodit',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
                isSuccess: (r) => r.success,
                process: (r) => r,
                defaultHandlerSuccess: function (data) {
                    (data.files || []).forEach((url) => this.selection.insertImage(url));
                },
            },
        });
    });
}

document.addEventListener('DOMContentLoaded', () => initEditors());
window.initEditors = initEditors;

Alpine.start();
