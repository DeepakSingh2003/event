import './bootstrap';

import Alpine from 'alpinejs';

Alpine.data('toast', () => ({
    visible: true,
    init() {
        setTimeout(() => {
            this.visible = false;
        }, 3500);
    },
}));

window.Alpine = Alpine;

Alpine.start();
