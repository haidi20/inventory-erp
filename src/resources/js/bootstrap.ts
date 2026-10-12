import axios, { type AxiosStatic } from "axios";

declare global {
    interface Window {
        axios: AxiosStatic;
        __csrf: string;
    }
}

window.axios = axios;

try {
    axios.defaults.withCredentials = true;
    axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

    const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content
        ?? window.__csrf
        ?? "";

    if (csrfToken) {
        axios.defaults.headers.common["X-CSRF-TOKEN"] = csrfToken;
    }
} catch (error) {
    console.error("Gagal mengatur header default Axios:", error);
}

export {};