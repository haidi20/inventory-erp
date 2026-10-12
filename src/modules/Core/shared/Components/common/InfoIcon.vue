<script setup lang="ts">
defineProps<{
    title?: string;
}>();

defineEmits(['click']);
</script>

<template>
    <span 
        class="info-icon" 
        @click="$emit('click')" 
        :title="title || 'Klik untuk bantuan (F1)'"
        role="button"
        tabindex="0"
        @keydown.enter.prevent="$emit('click')"
        @keydown.space.prevent="$emit('click')"
    >
        ⓘ
    </span>
</template>

<style scoped>
.info-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #475569;
    font-size: 12px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.2s;
    user-select: none;
    flex-shrink: 0;
}

.info-icon:hover {
    background: var(--primary, #3b82f6);
    color: white;
}

.info-icon:focus {
    outline: none;
    box-shadow: 0 0 0 2px white, 0 0 0 4px var(--primary, #3b82f6);
}
</style>
