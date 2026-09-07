<script setup>
import {
  computed
} from "vue";

import DealStatusBadge from
  "@/components/deals/DealStatusBadge.vue";

// ============================================================
// PROPS / EMITS
// ============================================================

const props = defineProps({
  deals: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits([
  "edit",
  "delete"
]);

// ============================================================
// FORMATTERS
// ============================================================

const formatCurrency = (value) => {
  return new Intl.NumberFormat(
    "pt-BR",
    {
      style: "currency",
      currency: "BRL"
    }
  ).format(
    Number(value) || 0
  );
};

const formatDate = (value) => {
  if (!value) {
    return "—";
  }

  const date = new Date(value);

  if (
    Number.isNaN(
      date.getTime()
    )
  ) {
    return "—";
  }

  return date.toLocaleDateString(
    "pt-BR"
  );
};

// ============================================================
// SUMMARY
// ============================================================

const summary = computed(() => {
  const result = {
    open: {
      count: 0,
      value: 0
    },

    won: {
      count: 0,
      value: 0
    },

    lost: {
      count: 0,
      value: 0
    },

    total: {
      count: 0,
      value: 0
    }
  };

  for (const deal of props.deals) {
    const value =
      Number(deal?.value) || 0;

    result.total.count++;
    result.total.value += value;

    if (
      result[
        deal?.status
      ]
    ) {
      result[
        deal.status
      ].count++;

      result[
        deal.status
      ].value += value;
    }
  }

  return result;
});

// ============================================================
// SUMMARY CARDS
// ============================================================

const summaryCards = computed(() => [
  {
    key: "open",
    label: "Abertos",
    icon: "🟡",
    className: "open-card",
    count:
      summary.value.open.count,
    value:
      summary.value.open.value
  },

  {
    key: "won",
    label: "Ganhos",
    icon: "🟢",
    className: "won-card",
    count:
      summary.value.won.count,
    value:
      summary.value.won.value
  },

  {
    key: "lost",
    label: "Perdidos",
    icon: "🔴",
    className: "lost-card",
    count:
      summary.value.lost.count,
    value:
      summary.value.lost.value
  },

  {
    key: "total",
    label: "Total geral",
    icon: "💰",
    className: "total-card",
    count:
      summary.value.total.count,
    value:
      summary.value.total.value
  }
]);

// ============================================================
// PLURAL
// ============================================================

const dealLabel = (count) => {
  return count === 1
    ? "negócio"
    : "negócios";
};
</script>

<template>
  <div class="deals-table">

    <!-- ================================================= -->
    <!-- RESUMO -->
    <!-- ================================================= -->

    <div class="summary">

      <div
        v-for="card in summaryCards"
        :key="card.key"
        class="summary-card"
        :class="card.className"
      >
        <div class="summary-icon">
          {{ card.icon }}
        </div>

        <div class="summary-content">

          <span class="summary-label">
            {{ card.label }}
          </span>

          <strong class="summary-value">
            {{
              formatCurrency(
                card.value
              )
            }}
          </strong>

          <small>
            {{ card.count }}
            {{
              dealLabel(
                card.count
              )
            }}
          </small>

        </div>
      </div>

    </div>

    <!-- ================================================= -->
    <!-- TABELA -->
    <!-- ================================================= -->

    <div class="table-wrapper">

      <table>

        <thead>
          <tr>
            <th>Título</th>
            <th>Cliente</th>
            <th>Valor</th>
            <th>Status</th>
            <th>Criado em</th>
            <th>Ações</th>
          </tr>
        </thead>

        <tbody>

          <!-- ================================================= -->
          <!-- NEGÓCIOS -->
          <!-- ================================================= -->

          <tr
            v-for="deal in deals"
            :key="deal.id"
          >
            <td>
              {{
                deal.title
                || "Sem título"
              }}
            </td>

            <td>
              {{
                deal.client?.name
                ?? "—"
              }}
            </td>

            <td>
              {{
                formatCurrency(
                  deal.value
                )
              }}
            </td>

            <td>
              <DealStatusBadge
                :status="deal.status"
              />
            </td>

            <td>
              {{
                formatDate(
                  deal.created_at
                )
              }}
            </td>

            <td>
              <div class="table-actions">

                <button
                  type="button"
                  class="action-btn edit-btn"
                  title="Editar negócio"
                  @click="emit('edit', deal)"
                >
                  ✏️ Editar
                </button>

                <button
                  type="button"
                  class="action-btn delete-btn"
                  title="Excluir negócio"
                  @click="emit('delete', deal)"
                >
                  🗑️ Excluir
                </button>

              </div>
            </td>
          </tr>

          <!-- ================================================= -->
          <!-- SEM DADOS -->
          <!-- ================================================= -->

          <tr
            v-if="!deals.length"
          >
            <td
              colspan="6"
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
.deals-table {
  width: 100%;
}

/* =====================================================
   RESUMO
===================================================== */

.summary {
  display: grid;

  grid-template-columns:
    repeat(
      4,
      minmax(0, 1fr)
    );

  gap: 16px;

  margin-bottom: 20px;
}

/* =====================================================
   SUMMARY CARD
===================================================== */

.summary-card {
  display: flex;
  align-items: center;
  gap: 14px;

  background: #ffffff;

  border-radius: 14px;

  padding: 18px;

  box-shadow:
    0 6px 16px
    rgba(0, 0, 0, 0.07);

  border-left:
    5px solid transparent;

  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease;
}

.summary-card:hover {
  transform:
    translateY(-2px);

  box-shadow:
    0 10px 22px
    rgba(0, 0, 0, 0.1);
}

.summary-icon {
  font-size: 25px;

  min-width: 35px;

  text-align: center;
}

.summary-content {
  display: flex;
  flex-direction: column;
  gap: 3px;

  min-width: 0;
}

.summary-label {
  font-size: 13px;

  color: #64748b;

  font-weight: 600;
}

.summary-value {
  font-size: 20px;

  color: #1e293b;

  word-break: break-word;
}

.summary-content small {
  font-size: 12px;

  color: #94a3b8;
}

/* =====================================================
   SUMMARY COLORS
===================================================== */

.open-card {
  border-left-color:
    #f59e0b;
}

.won-card {
  border-left-color:
    #10b981;
}

.lost-card {
  border-left-color:
    #ef4444;
}

.total-card {
  border-left-color:
    #2563eb;
}

/* =====================================================
   TABLE
===================================================== */

.table-wrapper {
  overflow-x: auto;

  color: #0f172a;

  background: #ffffff;

  border-radius: 14px;

  box-shadow:
    0 10px 20px
    rgba(0, 0, 0, 0.08);

  margin-bottom: 20px;
}

table {
  width: 100%;

  min-width: 900px;

  border-collapse: collapse;
}

th,
td {
  padding:
    14px 16px;

  text-align: left;
}

th {
  background: #f1f5f9;

  font-size: 13px;

  text-transform: uppercase;

  color: #475569;
}

tbody tr:not(:last-child) td {
  border-bottom:
    1px solid #e2e8f0;
}

tbody tr:hover {
  background: #f8fafc;
}

/* =====================================================
   ACTIONS
===================================================== */

.table-actions {
  display: flex;

  align-items: center;

  gap: 8px;
}

.action-btn {
  border: none;

  border-radius: 8px;

  padding:
    7px 10px;

  font-size: 12px;

  font-weight: 600;

  cursor: pointer;

  white-space: nowrap;

  transition:
    background 0.2s ease,
    transform 0.2s ease;
}

.action-btn:hover {
  transform:
    translateY(-1px);
}

.edit-btn {
  background: #eff6ff;

  color: #1d4ed8;
}

.edit-btn:hover {
  background: #dbeafe;
}

.delete-btn {
  background: #fef2f2;

  color: #dc2626;
}

.delete-btn:hover {
  background: #fee2e2;
}

/* =====================================================
   EMPTY
===================================================== */

.empty-row {
  text-align: center;

  padding: 30px;

  color: #64748b;

  font-style: italic;
}

/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 1100px) {
  .summary {
    grid-template-columns:
      repeat(
        2,
        minmax(0, 1fr)
      );
  }
}

@media (max-width: 600px) {
  .summary {
    grid-template-columns:
      1fr;
  }

  .table-actions {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>