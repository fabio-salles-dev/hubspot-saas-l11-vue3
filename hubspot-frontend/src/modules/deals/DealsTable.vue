<script setup>
import { computed } from "vue";
import DealStatusBadge from "@/components/deals/DealStatusBadge.vue";

const props = defineProps({
  deals: {
    type: Array,
    default: () => [],
  },
});

// =====================================================
// FORMATAÇÃO DE MOEDA
// =====================================================

const formatCurrency = (value) => {
  return Number(value || 0).toLocaleString("pt-BR", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

// =====================================================
// TOTAL GERAL
// =====================================================

const totalValue = computed(() => {
  return (props.deals || []).reduce((total, deal) => {
    return total + Number(deal.value || 0);
  }, 0);
});

// =====================================================
// TOTAL ABERTOS
// =====================================================

const openTotal = computed(() => {
  return (props.deals || [])
    .filter((deal) => deal.status === "open")
    .reduce((total, deal) => {
      return total + Number(deal.value || 0);
    }, 0);
});

// =====================================================
// TOTAL GANHOS
// =====================================================

const wonTotal = computed(() => {
  return (props.deals || [])
    .filter((deal) => deal.status === "won")
    .reduce((total, deal) => {
      return total + Number(deal.value || 0);
    }, 0);
});

// =====================================================
// TOTAL PERDIDOS
// =====================================================

const lostTotal = computed(() => {
  return (props.deals || [])
    .filter((deal) => deal.status === "lost")
    .reduce((total, deal) => {
      return total + Number(deal.value || 0);
    }, 0);
});

// =====================================================
// QUANTIDADES
// =====================================================

const openCount = computed(() => {
  return (props.deals || []).filter(
    (deal) => deal.status === "open"
  ).length;
});

const wonCount = computed(() => {
  return (props.deals || []).filter(
    (deal) => deal.status === "won"
  ).length;
});

const lostCount = computed(() => {
  return (props.deals || []).filter(
    (deal) => deal.status === "lost"
  ).length;
});

const totalCount = computed(() => {
  return (props.deals || []).length;
});

// =====================================================
// DATA
// =====================================================

const formatDate = (date) => {
  if (!date) {
    return "—";
  }

  return new Date(date).toLocaleDateString("pt-BR");
};
</script>

<template>
  <div>

    <!-- =================================================
         RESUMO DOS VALORES
    ================================================== -->

    <div class="summary">

      <!-- ABERTOS -->
      <div class="summary-card open-card">

        <div class="summary-icon">
          🟡
        </div>

        <div class="summary-content">

          <span class="summary-label">
            Abertos
          </span>

          <strong class="summary-value">
            R$ {{ formatCurrency(openTotal) }}
          </strong>

          <small>
            {{ openCount }}
            {{ openCount === 1 ? "negócio" : "negócios" }}
          </small>

        </div>

      </div>


      <!-- GANHOS -->
      <div class="summary-card won-card">

        <div class="summary-icon">
          🟢
        </div>

        <div class="summary-content">

          <span class="summary-label">
            Ganhos
          </span>

          <strong class="summary-value">
            R$ {{ formatCurrency(wonTotal) }}
          </strong>

          <small>
            {{ wonCount }}
            {{ wonCount === 1 ? "negócio" : "negócios" }}
          </small>

        </div>

      </div>


      <!-- PERDIDOS -->
      <div class="summary-card lost-card">

        <div class="summary-icon">
          🔴
        </div>

        <div class="summary-content">

          <span class="summary-label">
            Perdidos
          </span>

          <strong class="summary-value">
            R$ {{ formatCurrency(lostTotal) }}
          </strong>

          <small>
            {{ lostCount }}
            {{ lostCount === 1 ? "negócio" : "negócios" }}
          </small>

        </div>

      </div>


      <!-- TOTAL GERAL -->
      <div class="summary-card total-card">

        <div class="summary-icon">
          💰
        </div>

        <div class="summary-content">

          <span class="summary-label">
            Total geral
          </span>

          <strong class="summary-value">
            R$ {{ formatCurrency(totalValue) }}
          </strong>

          <small>
            {{ totalCount }}
            {{ totalCount === 1 ? "negócio" : "negócios" }}
          </small>

        </div>

      </div>

    </div>


    <!-- =================================================
         TABELA
    ================================================== -->

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

          <!-- NEGÓCIOS -->

          <tr
            v-for="deal in deals"
            :key="deal.id"
          >

            <td>
              {{ deal.title }}
            </td>

            <td>
              {{ deal.client?.name ?? "—" }}
            </td>

            <td>
              R$ {{ formatCurrency(deal.value) }}
            </td>

            <td>
              <DealStatusBadge
                :status="deal.status"
              />
            </td>

            <td>
              {{ formatDate(deal.created_at) }}
            </td>

          </tr>


          <!-- SEM DADOS -->

          <tr
            v-if="!deals || deals.length === 0"
          >

            <td
              colspan="5"
              class="empty-row"
            >
              Nenhum negócio encontrado.
            </td>

          </tr>

        </tbody>

      </table>

    </div>

  </div>
</template>


<style scoped>

/* =====================================================
   RESUMO
===================================================== */

.summary {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 20px;
}


/* =====================================================
   CARD DO RESUMO
===================================================== */

.summary-card {
  display: flex;
  align-items: center;
  gap: 14px;

  background: #ffffff;

  border-radius: 14px;

  padding: 18px;

  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.07);

  border-left: 5px solid transparent;

  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease;
}

.summary-card:hover {
  transform: translateY(-2px);

  box-shadow:
    0 10px 22px rgba(0, 0, 0, 0.10);
}


/* =====================================================
   ÍCONE
===================================================== */

.summary-icon {
  font-size: 25px;
  min-width: 35px;
  text-align: center;
}


/* =====================================================
   CONTEÚDO
===================================================== */

.summary-content {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.summary-label {
  font-size: 13px;
  color: #64748b;
  font-weight: 600;
}

.summary-value {
  font-size: 20px;
  color: #1e293b;
}

.summary-content small {
  font-size: 12px;
  color: #94a3b8;
}


/* =====================================================
   CORES DOS CARDS
===================================================== */

.open-card {
  border-left-color: #f59e0b;
}

.won-card {
  border-left-color: #10b981;
}

.lost-card {
  border-left-color: #ef4444;
}

.total-card {
  border-left-color: #2563eb;
}


/* =====================================================
   TABELA
===================================================== */

.table-wrapper {
  color: rgb(8, 7, 8);

  overflow-x: auto;

  background: #ffffff;

  border-radius: 14px;

  box-shadow:
    0 10px 20px rgba(0, 0, 0, 0.08);

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


/* =====================================================
   SEM DADOS
===================================================== */

.empty-row {
  text-align: center;

  padding: 30px;

  color: #64748b;

  font-style: italic;
}


/* =====================================================
   RESPONSIVO
===================================================== */

@media (max-width: 1100px) {

  .summary {
    grid-template-columns: repeat(2, 1fr);
  }

}


@media (max-width: 600px) {

  .summary {
    grid-template-columns: 1fr;
  }

}

</style>