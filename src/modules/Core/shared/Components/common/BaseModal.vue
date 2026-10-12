<script setup lang="ts">
/**
 * BaseModal — wrapper modal konsisten untuk seluruh aplikasi.
 *
 * Ukuran yang tersedia:
 *   md   — 550px  (default, form sederhana)
 *   lg   — 750px  (form sedang / multi-kolom)
 *   xl   — 1000px (form kompleks / tabel embedded)
 *   full — 100vw - 40px (halaman penuh)
 *
 * Contoh pemakaian:
 *   <BaseModal size="lg" @close="vm.closeForm">
 *     <div class="modal-header">...</div>
 *     <form ...>
 *       <div class="modal-body">...</div>
 *       <div class="modal-footer">...</div>
 *     </form>
 *   </BaseModal>
 */

type ModalSize = 'md' | 'lg' | 'xl' | 'full';

withDefaults(defineProps<{
    size?: ModalSize;
}>(), {
    size: 'md',
});

const emit = defineEmits<{
    close: [];
}>();
</script>

<template>
    <Teleport to="body">
        <div class="modal-overlay" @click.self="emit('close')">
            <div class="modal-card" :class="`modal-${size}`">
                <slot />
            </div>
        </div>
    </Teleport>
</template>
