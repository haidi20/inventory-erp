<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    manual?: boolean;
}>();

const emit = defineEmits(['change-page']);

const handleClick = (url: string | null, event: Event) => {
    if (props.manual && url) {
        event.preventDefault();
        emit('change-page', url);
    }
};
</script>

<template>
    <div v-if="links.length > 3" class="pagination-container">
        <div class="pagination-flex">
            <template v-for="(link, k) in links" :key="k">
                <div v-if="link.url === null" 
                     class="pagination-link disabled" 
                     v-html="link.label" />
                <a v-else-if="manual"
                   href="#"
                   class="pagination-link"
                   :class="{ 'active': link.active }"
                   @click.prevent="handleClick(link.url, $event)"
                   v-html="link.label" />
                <Link v-else 
                      class="pagination-link" 
                      :class="{ 'active': link.active }" 
                      :href="link.url || '#'" 
                      v-html="link.label" />
            </template>
        </div>
    </div>
</template>

<style scoped>
.pagination-container {
    margin-top: 24px;
    display: flex;
    justify-content: center;
}

.pagination-flex {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.pagination-link {
    padding: 8px 14px;
    min-width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    background: #fff;
    border: 1px solid var(--border);
    transition: all 0.2s;
    text-decoration: none;
    cursor: pointer;
    user-select: none;
}

.pagination-link:hover:not(.disabled):not(.active) {
    border-color: var(--primary);
    color: var(--primary);
    background: var(--primary-light);
    transform: translateY(-2px);
}

.pagination-link.active {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.pagination-link.disabled {
    color: var(--muted);
    opacity: 0.5;
    cursor: default;
    background: #f8fafc;
}

/* responsive adjustments */
@media (max-width: 640px) {
    .pagination-link {
        padding: 6px 10px;
        font-size: 12px;
        min-width: 32px;
        height: 32px;
    }
}
</style>
