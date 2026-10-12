import axios from 'axios';

process.env.KLINIK_RUNTIME_ENV = process.env.KLINIK_RUNTIME_ENV ?? 'sandbox';
process.env.KLINIK_TEST_BASE_URL = process.env.KLINIK_TEST_BASE_URL ?? 'http://localhost:8000';

axios.defaults.headers.common['X-Klinik-Environment'] = process.env.KLINIK_RUNTIME_ENV;
