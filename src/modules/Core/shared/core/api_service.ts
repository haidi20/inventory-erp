import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

interface CustomPageProps {
    auth?: {
        api_token?: string;
    };
    [key: string]: any;
}

const apiService = axios.create({
    baseURL: '/api/v1',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
});

let isHandlingUnauthorized = false;

function getNodeEnv(name: string): string | undefined {
    try {
        return typeof process !== 'undefined' ? process.env?.[name] : undefined;
    } catch (error) {
        return undefined;
    }
}

function resolveRuntimeContext(): { environment?: string; company_uuid?: string } {
    try {
        const page = usePage<CustomPageProps & {
            app_context?: {
                runtime_environment?: string;
                company_uuid?: string;
            };
        }>();

        return {
            environment: page?.props?.app_context?.runtime_environment ?? getNodeEnv('KLINIK_RUNTIME_ENV'),
            company_uuid: page?.props?.app_context?.company_uuid,
        };
    } catch (error) {
        return {
            environment: getNodeEnv('KLINIK_RUNTIME_ENV'),
        };
    }
}

function redirectToLogout(): void {
    if (isHandlingUnauthorized || typeof window === 'undefined') {
        return;
    }

    isHandlingUnauthorized = true;

    const logoutForm = document.querySelector('form[action="/logout"]') as HTMLFormElement | null;
    if (logoutForm) {
        logoutForm.submit();
        return;
    }

    window.location.assign('/login');
}

// Request interceptor to add JWT token
apiService.interceptors.request.use(
    (config) => {
        try {
            const page = usePage<CustomPageProps>();
            const token = page?.props?.auth?.api_token;

            if (token) {
                config.headers.Authorization = `Bearer ${token}`;
            }
        } catch (e) {
            // Fallback: diluar Vue context
        }

        const runtimeContext = resolveRuntimeContext();

        if (runtimeContext.environment) {
            config.headers['X-Klinik-Environment'] = runtimeContext.environment;
        }

        if (runtimeContext.company_uuid) {
            config.headers['X-Klinik-Company-Uuid'] = runtimeContext.company_uuid;
        }

        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response interceptor to handle auth errors
apiService.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error.response?.status;

        if (status === 401) {
            console.error('Unauthorized access - sesi habis, mengalihkan ke login.');
            redirectToLogout();
        }

        if (status === 403) {
            console.error('Forbidden - Anda tidak memiliki izin untuk mengakses resource ini.');
        }

        return Promise.reject(error);
    }
);

export default apiService;
