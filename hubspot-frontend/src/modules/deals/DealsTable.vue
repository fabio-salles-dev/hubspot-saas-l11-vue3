<script setup>
import { computed } from 'vue'
import DealStatusBadge from '@/components/deals/DealStatusBadge.vue'

const props = defineProps({
  deals: {
    type: Array,
    required: true
  }
})

const totalValue = computed(() =>
  props.deals.reduce((total, deal) => total + Number(deal.value || 0), 0)
)
</script>

<template>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Título</th>
          <th>Cliente</th>
          <th>Valor</th>
          <th>Status</th>
          <th>Criado em</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="deal in deals" :key="deal.id">
          <td>{{ deal.title }}</td>
          <td>{{ deal.client?.name ?? '—' }}</td>
          <td>R$ {{ Number(deal.value).toLocaleString('pt-BR') }}</td>
          <td>
            <DealStatusBadge :status="deal.status" />
          </td>
          <td>
            {{ new Date(deal.created_at).toLocaleDateString('pt-BR') }}
          </td>
        </tr>

        <!-- Linha TOTAL -->
        <tr class="total-row">
          <td colspan="2"><strong>TOTAL</strong></td>
          <td colspan="3">
            <strong>R$ {{ totalValue.toLocaleString('pt-BR') }}</strong>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.table-wrapper {
  color: rgb(8, 7, 8);
  overflow-x: auto;
  background: #ffffff;
  border-radius: 14px;
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
  padding-bottom: 16px;
  margin-bottom: 20px;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  padding: 14px 16px;
  text-align: left;
}

th {
  background: #f1f5f9;
  font-size: 13px;
  text-transform: uppercase;
  color: #475569;
}

tr:not(:last-child) td {
  border-bottom: 1px solid #e2e8f0;
}

/* TOTAL */
.total-row {
  background-color: #e0f2fe;
}

.total-row td {
  font-size: 15px;
}
</style>
