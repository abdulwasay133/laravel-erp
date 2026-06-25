import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.axios.interceptors.request.use((config) => {
    if (!config._skipLoading) {
        window.showLoading?.();
    }
    return config;
});

window.axios.interceptors.response.use(
    (response) => {
        if (!response.config._skipLoading) {
            window.hideLoading?.();
        }
        return response;
    },
    (error) => {
        if (!error.config?._skipLoading) {
            window.hideLoading?.();
        }
        return Promise.reject(error);
    }
);
