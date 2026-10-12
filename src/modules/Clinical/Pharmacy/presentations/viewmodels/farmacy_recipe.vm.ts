import type { AssessmentSection } from './farmacy_recipe.state';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { farmacyDependencies } from '@modules/Clinical/Pharmacy/domain/di/farmacy.di';
import type { 
    RecipeModel, 
    MedicationItem, 
    PatientProfile,
    PrescriptionAssessment,
    RecipeFilters
} from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

export const useFarmacyRecipeVM = defineStore('farmacy-recipe-vm', () => {
    const { getRecipesUseCase, getRecipeDetailsUseCase, getPatientProfileUseCase } = farmacyDependencies;
    
    // --- States ---
    const recipes = ref<RecipeModel[]>([]);
    const showDetailModal = ref(false);
    const selectedMedications = ref<MedicationItem[]>([]);
    const selectedProfile = ref<PatientProfile | null>(null);
    const isLoading = ref(false);

    const recipeFee = 5000;
    const medicationSubtotal = computed(() =>
        selectedMedications.value.reduce((total, item) => total + item.subtotal, 0),
    );
    const grandTotal = computed(() => medicationSubtotal.value + recipeFee);

    const formatIDR = (value: number) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);

    // --- Filter State ---
    const filters = ref<RecipeFilters>({
        status: 'Semua Status',
        date: '',
        search: ''
    });

    // --- UI Configuration States ---
    const activeDetailTab = ref('resep');
    const recipeDetailTabs = ref([
        { label: 'Resep', value: 'resep' },
        { label: 'Pelayanan Resep', value: 'pelayanan' },
    ]);

    const assessmentSections = ref<AssessmentSection[]>([
        { key: 'administrative', label: 'Pengkajian Administratif', icon: 'fa-file-invoice', target: 'tidak' },
        { key: 'pharmaceutical', label: 'Pengkajian Farmasetis', icon: 'fa-capsules', target: 'tidak' },
        { key: 'clinical', label: 'Pengkajian Klinis', icon: 'fa-stethoscope', target: 'ya' },
        { key: 'five_rights', label: 'Pengkajian 5 Benar', icon: 'fa-check-double', target: 'tidak' },
    ]);

    const paymentMethods = ref([
        { label: 'Tunai', value: 'cash' },
        { label: 'BPJS', value: 'bpjs' },
        { label: 'Kredit / Invoice', value: 'credit' },
    ]);

    // --- Dropdown Options ---
    const pharmacists = ref([
        { id: '1', name: 'Nilam Sylvia Ramadhani, A. Md. Farm' },
        { id: '2', name: 'Riska Sari, A. Md. RMIK' },
        { id: '3', name: 'Andi Wijaya, S. Farm' },
    ]);

    const warehouses = ref([
        { id: 'G01', name: 'Gudang Utama' },
        { id: 'G02', name: 'Gudang Farmasi Lt. 1' },
        { id: 'G03', name: 'Gudang IGD' },
    ]);

    const businessUnitSuggestions = ref(['Klinik Utama', 'Klinik Berkat Medika', 'Apotek Pusat']);

    // --- Form States for Detail Modal ---
    const recipeForm = ref({
        pharmacist_id: '',
        unit_name: '',
        warehouse_id: '',
        payment_method: 'cash'
    });

    // --- Assessment Form State ---
    const assessmentForm = ref<PrescriptionAssessment>({
        administrative: [
            { field: 'Tanggal Resep', result: 'ya', note: '' },
            { field: 'Nama Pasien', result: 'ya', note: '' },
            { field: 'No. RM', result: 'ya', note: '' },
            { field: 'Tanggal Lahir', result: 'ya', note: '' },
            { field: 'Berat Badan', result: 'ya', note: '' },
            { field: 'Nama Dokter', result: 'ya', note: '' },
            { field: 'Paraf/TTD Dokter', result: 'ya', note: '' },
        ],
        pharmaceutical: [
            { field: 'Nama Obat', result: 'ya', note: '' },
            { field: 'Dosis', result: 'ya', note: '' },
            { field: 'Bentuk Sediaan', result: 'ya', note: '' },
            { field: 'Jumlah', result: 'ya', note: '' },
            { field: 'Aturan Pakai', result: 'ya', note: '' },
            { field: 'Cara Pakai', result: 'ya', note: '' },
        ],
        clinical: [
            { field: 'Kontraindikasi', result: 'tidak', note: '' },
            { field: 'Interaksi Obat', result: 'tidak', note: '' },
            { field: 'Alergi Obat', result: 'tidak', note: '' },
            { field: 'Duplikasi Obat', result: 'tidak', note: '' },
        ],
        five_rights: [
            { field: 'Benar Identitas Pasien', result: 'ya', note: '' },
            { field: 'Benar Nama dan Jumlah Obat', result: 'ya', note: '' },
            { field: 'Benar Dosis', result: 'ya', note: '' },
            { field: 'Benar Rute Pemberian', result: 'ya', note: '' },
            { field: 'Benar Waktu Pemberian', result: 'ya', note: '' },
        ]
    });

    // --- Actions ---
    const fetch = async () => {
        isLoading.value = true;
        const result = await getRecipesUseCase.execute(filters.value);
        result.fold(() => {}, (data) => { recipes.value = data; });
        isLoading.value = false;
    };

    const fetchDetail = async (uuid: string) => {
        isLoading.value = true;
        activeDetailTab.value = 'resep'; 
        
        const [detailsResult, profileResult] = await Promise.all([
            getRecipeDetailsUseCase.execute(uuid),
            getPatientProfileUseCase.execute(uuid)
        ]);
        
        detailsResult.fold(() => {}, (data) => { selectedMedications.value = data; });
        profileResult.fold(() => {}, (data) => { 
            selectedProfile.value = data;
            recipeForm.value.pharmacist_id = pharmacists.value[0].id;
            recipeForm.value.unit_name = data.unit;
            recipeForm.value.warehouse_id = warehouses.value[0].id;
        });
        
        isLoading.value = false;
    };

    const openRecipeDetail = async (recipe: RecipeModel) => {
        await fetchDetail(recipe.uuid);
        showDetailModal.value = true;
    };

    const closeRecipeDetail = () => {
        showDetailModal.value = false;
    };

    const printRecipe = (recipe: RecipeModel) => {
        void recipe;
    };

    const printMedication = (medication: MedicationItem) => {
        void medication;
    };

    const applyFilters = () => {
        void fetch();
    };

    const setActiveTab = (tab: string) => {
        activeDetailTab.value = tab;
    };

    return { 
        recipes, 
        showDetailModal,
        selectedMedications, 
        selectedProfile, 
        isLoading, 
        recipeFee,
        medicationSubtotal,
        grandTotal,
        filters,
        activeDetailTab,
        recipeDetailTabs,
        assessmentSections,
        paymentMethods,
        pharmacists,
        warehouses,
        businessUnitSuggestions,
        recipeForm,
        assessmentForm,
        fetch, 
        fetchDetail,
        openRecipeDetail,
        closeRecipeDetail,
        printRecipe,
        printMedication,
        formatIDR,
        applyFilters,
        setActiveTab
    };
});
