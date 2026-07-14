import Alpine from 'alpinejs';
import { Jodit } from 'jodit';
import 'jodit/es2021/jodit.min.css';

window.Alpine = Alpine;

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
