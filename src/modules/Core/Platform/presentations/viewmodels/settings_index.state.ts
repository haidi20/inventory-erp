export interface SettingsMenuState {
    label: string;
    url: string;
    icon: string;
}

export const SETTINGS_INDEX_MENUS: SettingsMenuState[] = [
    { label: 'Backup Database', url: '/settings/backup', icon: 'fa-database' },
    { label: 'ICD-10', url: '/settings/icd10', icon: 'fa-notes-medical' },
    { label: 'SATUSEHAT', url: '/satusehat/settings', icon: 'fa-hospital' },
    { label: 'BPJS', url: '/bpjs/settings', icon: 'fa-id-card' },
];
