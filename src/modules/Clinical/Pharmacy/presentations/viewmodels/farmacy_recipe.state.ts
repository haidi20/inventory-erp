import type { PrescriptionAssessment } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';
// farmacy_recipe.state.ts - state types untuk FarmacyRecipeVM
export interface RecipeFilters {
    status?: string;
    date?: string;
    search?: string;
}

export interface RecipeState {
    isLoading: boolean;
    activeDetailTab: string;
}

export const RECIPE_STATUS_OPTIONS = [
    { id: 'Semua Status', text: 'Semua Status' },
    { id: 'pending',      text: 'Pending' },
    { id: 'proses',       text: 'Proses' },
    { id: 'selesai',      text: 'Selesai' },
] as const;

export const RECIPE_FILTER_STATUS_OPTIONS = [
    { id: 'Semua Status', text: 'Semua Status' },
    { id: 'Selesai', text: 'Selesai' },
    { id: 'Menunggu', text: 'Menunggu' },
    { id: 'Dibatalkan', text: 'Dibatalkan' },
] as const;

export const RECIPE_STATUS_BADGES: Record<string, string> = {
    completed: 'badge-success',
    pending: 'badge-warning',
    cancelled: 'badge-danger',
};

export const RECIPE_STATUS_LABELS: Record<string, string> = {
    completed: 'Selesai',
    pending: 'Menunggu',
    cancelled: 'Dibatalkan',
};

export interface AssessmentSection {
    key: keyof PrescriptionAssessment;
    label: string;
    icon: string;
    target: 'ya' | 'tidak';
}
