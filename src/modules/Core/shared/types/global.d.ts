import { Chart } from 'chart.js';

declare global {
    interface Window {
        Chart: typeof Chart;
        Livewire: any;
    }
}

export {};
