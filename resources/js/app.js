import './bootstrap';
import Swal from 'sweetalert2';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
window.Swal = Swal;

Alpine.store('loading', {
    isVisible: false,
    message: 'Loading...',

    show(msg) {
        this.message = msg || 'Loading...';
        this.isVisible = true;
    },

    hide() {
        this.isVisible = false;
    },
});

window.showLoading = (msg) => Alpine.store('loading').show(msg);
window.hideLoading = () => Alpine.store('loading').hide();

window.withLoading = async (promise, msg) => {
    showLoading(msg);
    try {
        return await promise;
    } finally {
        hideLoading();
    }
};

Alpine.start();
