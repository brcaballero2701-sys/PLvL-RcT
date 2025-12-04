import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// ✅ fuerza https usando variable de Vite
window.axios.defaults.baseURL = import.meta.env.VITE_APP_URL || '';