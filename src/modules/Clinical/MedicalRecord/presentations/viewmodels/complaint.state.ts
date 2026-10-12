// complaint.state.ts — state types untuk ComplaintVM
export interface ComplaintFormData {
    subject: string;
    content: string;
    type: 'pengaduan' | 'saran' | 'pertanyaan';
    is_anonymous: boolean;
}

export interface ComplaintState {
    isFormOpen: boolean;
}

export const COMPLAINT_TYPES = [
    { id: 'pengaduan', text: 'Pengaduan' },
    { id: 'saran',     text: 'Saran' },
    { id: 'pertanyaan', text: 'Pertanyaan' },
] as const;
