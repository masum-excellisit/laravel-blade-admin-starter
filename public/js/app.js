/**
 * App JS — no build step. Depends on window.Alpine, window.Sortable, window.Jodit
 * loaded before this file (see partials/assets).
 */
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

    Alpine.data('sidebarScroll', () => ({
        canScrollUp: false,
        canScrollDown: false,
        el: null,
        observer: null,
        init() {
            this.$nextTick(() => {
                this.el = this.$refs.navScroll;
                if (!this.el) return;

                this.update();
                requestAnimationFrame(() => {
                    this.scrollActiveIntoView();
                    this.update();
                });

                this.observer = new ResizeObserver(() => this.update());
                this.observer.observe(this.el);
                if (this.el.firstElementChild) {
                    this.observer.observe(this.el.firstElementChild);
                }

                // Recalculate when the sidebar expands/collapses.
                this.$watch('$root.collapsed', () => {
                    this.$nextTick(() => this.update());
                });
            });
        },
        update() {
            const el = this.el || this.$refs.navScroll;
            if (!el) return;
            this.el = el;
            const max = el.scrollHeight - el.clientHeight;
            const top = el.scrollTop;
            this.canScrollUp = top > 4;
            this.canScrollDown = max > 4 && top < max - 4;
        },
        scrollActiveIntoView() {
            const el = this.el || this.$refs.navScroll;
            if (!el) return;
            const active = el.querySelector('a.brand-gradient');
            if (!active) return;
            const elRect = el.getBoundingClientRect();
            const activeRect = active.getBoundingClientRect();
            const fullyVisible = activeRect.top >= elRect.top + 8 && activeRect.bottom <= elRect.bottom - 8;
            if (!fullyVisible) {
                active.scrollIntoView({ block: 'nearest', inline: 'nearest' });
            }
        },
        destroy() {
            this.observer?.disconnect();
        },
    }));
});

function initEditors(root = document) {
    if (!window.Jodit) return;
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
