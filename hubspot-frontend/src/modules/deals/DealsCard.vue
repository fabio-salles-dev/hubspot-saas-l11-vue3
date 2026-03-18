<script setup>
import DealStatusBadge from '@/components/deals/DealStatusBadge.vue'

defineProps({
  deals: {
    type: Array,
    required: true
  }
})
</script>

<template>
  <div class="deals-cards">
    <div v-for="deal in deals" :key="deal.id" class="deal-card">
      <div class="card-header">
        <h3>{{ deal.title }}</h3>
        <DealStatusBadge :status="deal.status" />
      </div>

      <p class="value">
        💰 R$ {{ Number(deal.value).toLocaleString('pt-BR') }}
      </p>

      <p class="client">
        👤 {{ deal.client?.name ?? 'Sem cliente' }}
      </p>
    </div>
  </div>
</template>

<style scoped>
.deals-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 16px;
}

.deal-card {
  background: #ffffff;
  border-radius: 14px;
  padding: 20px;
  box-shadow: 0 10px 20px rgba(0, 0, 0, .08);
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.value {
  font-size: 18px;
  font-weight: 700;
  color: #1e3a8a;
}

.client {
  font-size: 14px;
  color: #475569;
}

.deals-cards {
  color: #1e3a8a;
}
</style>
