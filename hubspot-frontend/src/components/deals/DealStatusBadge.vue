<script setup>
defineProps({
  status: {
    type: String,
    required: true
  }
})

const statusMap = {
  open:  { label: 'Aberto', class: 'badge-open' },
  won:   { label: 'Ganho', class: 'badge-won' },
  lost:  { label: 'Perdido', class: 'badge-lost' }
}
</script>

<template>
  <span
    class="status-badge"
    :class="statusMap[status]?.class"
  >
    {{ statusMap[status]?.label ?? status }}
  </span>
</template>

<style scoped>
.status-badge {
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
}

.badge-open {
  background: #e0f2fe;
  color: #0369a1;
}

.badge-won {
  background: #dcfce7;
  color: #166534;
}

.badge-lost {
  background: #fee2e2;
  color: #991b1b;
}
</style>
