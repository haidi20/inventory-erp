<script setup lang="ts">
import { useComplaintVM } from '@modules/Clinical/MedicalRecord/presentations/viewmodels/complaint.vm';
import ComplaintForm from '@modules/Clinical/MedicalRecord/presentations/views/Partials/ComplaintForm.vue';

const vm = useComplaintVM();
</script>

<template>
    <div>
        <div class="header">
            <h3>Layanan Pengaduan & Saran</h3>
            <button @click="vm.openForm" class="btn btn-primary">Buat Pengaduan Baru</button>
        </div>

        <ComplaintForm v-if="vm.isFormOpen" />
    </div>
</template>

<style scoped>
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
</style>
