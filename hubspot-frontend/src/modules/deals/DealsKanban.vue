<script setup>
import {
  ref,
  watch,
  onMounted,
  onBeforeUnmount
} from "vue";

import draggable from "vuedraggable";

import {
  updateDeal
} from "./deals.service";

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
  "updated"
]);

// ============================================================
// CONFIGURAÇÃO DAS COLUNAS
// ============================================================

const kanbanColumns = [
  {
    status: "open",
    title: "Abertos",
    icon: "🟡",
    className: "open"
  },
  {
    status: "won",
    title: "Ganhos",
    icon: "🟢",
    className: "success"
  },
  {
    status: "lost",
    title: "Perdidos",
    icon: "🔴",
    className: "danger"
  }
];

// ============================================================
// ESTADO
// ============================================================

const columns = ref({
  open: [],
  won: [],
  lost: []
});

const updatingDealId = ref(null);

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

// ============================================================
// MAP DEALS
// ============================================================

const mapDealsToColumns = () => {
  const deals = props.deals ?? [];

  columns.value = {
    open: deals.filter(
      deal =>
        deal.status === "open"
    ),

    won: deals.filter(
      deal =>
        deal.status === "won"
    ),

    lost: deals.filter(
      deal =>
        deal.status === "lost"
    )
  };
};

watch(
  () => props.deals,
  mapDealsToColumns,
  {
    immediate: true,
    deep: true
  }
);

// ============================================================
// UPDATE STATUS
// ============================================================

const updateDealStatus = async (
  deal,
  status
) => {
  if (!deal?.id) {
    throw new Error(
      "Negócio sem ID."
    );
  }

  updatingDealId.value =
    deal.id;

  try {
    await updateDeal(
      deal.id,
      {
        status
      }
    );

  } finally {
    updatingDealId.value =
      null;
  }
};

// ============================================================
// CHANGE / DROP
// ============================================================

const onChange = async (
  event,
  status
) => {
  if (!event?.added) {
    return;
  }

  const deal =
    event.added.element;

  if (!deal?.id) {
    console.error(
      "Negócio arrastado sem ID."
    );

    mapDealsToColumns();

    return;
  }

  const previousStatus =
    deal.status;

  deal.status =
    status;

  try {
    await updateDealStatus(
      deal,
      status
    );

    emit("updated");

  } catch (error) {
    console.error(
      "Erro ao atualizar status do negócio:",
      error
    );

    deal.status =
      previousStatus;

    mapDealsToColumns();

    emit("updated");
  }
};

// ============================================================
// REALTIME
// ============================================================

const handleDealUpdated = () => {
  emit("updated");
};

// ============================================================
// ECHO CHANNEL
// ============================================================

let dealsChannel = null;

onMounted(() => {
  if (!window.Echo) {
    return;
  }

  dealsChannel =
    window.Echo
      .channel("deals");

  dealsChannel.listen(
    ".deal.updated",
    handleDealUpdated
  );
});

onBeforeUnmount(() => {
  if (!dealsChannel) {
    return;
  }

  dealsChannel.stopListening(
    ".deal.updated",
    handleDealUpdated
  );

  dealsChannel = null;
});
</script>

<template>
  <div class="kanban">

    <div
      v-for="column in kanbanColumns"
      :key="column.status"
      class="column"
    >
      <div class="column-header">
        <h3>
          {{ column.icon }}
          {{ column.title }}
        </h3>

        <span class="column-count">
          {{
            columns[
              column.status
            ].length
          }}
        </span>
      </div>

      <draggable
        :list="
          columns[
            column.status
          ]
        "
        group="deals"
        item-key="id"
        class="drop-zone"
        @change="
          event =>
            onChange(
              event,
              column.status
            )
        "
      >
        <template
          #item="{ element }"
        >
          <div
            class="card"
            :class="
              column.className
            "
          >
            <div class="card-header">
              <h4>
                {{
                  element.title
                  || "Sem título"
                }}
              </h4>

              <span
                v-if="
                  updatingDealId
                  === element.id
                "
                class="saving"
              >
                Salvando...
              </span>
            </div>

            <p class="value">
              💰
              {{
                formatCurrency(
                  element.value
                )
              }}
            </p>

            <small>
              👤
              {{
                element.client?.name
                ?? "Sem cliente"
              }}
            </small>
          </div>
        </template>
      </draggable>

      <div
        v-if="
          !columns[
            column.status
          ].length
        "
        class="empty-column"
      >
        Nenhum negócio
      </div>
    </div>

  </div>
</template>

<style scoped>
.kanban {
  display: grid;

  grid-template-columns:
    repeat(
      3,
      minmax(280px, 1fr)
    );

  gap: 16px;

  overflow-x: auto;

  padding: 16px;

  border:
    1px solid #cbd5e1;

  border-radius: 12px;

  margin:
    12px 0;
}

.column {
  min-width: 280px;

  background: #f8fafc;

  border:
    1px solid #e2e8f0;

  border-radius: 12px;

  padding: 12px;
}

.column-header {
  display: flex;

  align-items: center;
  justify-content: space-between;

  gap: 12px;

  margin-bottom: 12px;
}

.column-header h3 {
  margin: 0;

  font-size: 1rem;

  color: #334155;
}

.column-count {
  min-width: 28px;

  padding:
    4px 8px;

  border-radius: 999px;

  background: #e2e8f0;

  color: #475569;

  text-align: center;

  font-size: 12px;

  font-weight: 700;
}

.drop-zone {
  min-height: 60px;
}

.card {
  background: #ffffff;

  padding: 12px;

  border-radius: 10px;

  margin-bottom: 10px;

  cursor: grab;

  border-left:
    4px solid #f59e0b;

  box-shadow:
    0 2px 6px
    rgba(0, 0, 0, 0.05);

  transition:
    transform 0.15s ease,
    box-shadow 0.15s ease,
    opacity 0.15s ease;
}

.card:hover {
  transform:
    translateY(-1px);

  box-shadow:
    0 4px 10px
    rgba(0, 0, 0, 0.08);
}

.card:active {
  cursor: grabbing;
}

.card.open {
  border-left-color:
    #f59e0b;
}

.card.success {
  border-left-color:
    #10b981;
}

.card.danger {
  border-left-color:
    #ef4444;
}

.card-header {
  display: flex;

  align-items: flex-start;
  justify-content: space-between;

  gap: 8px;
}

.card h4 {
  margin:
    0 0 6px;

  font-size: 1rem;

  color: #1e3a8a;
}

.saving {
  font-size: 11px;

  color: #64748b;

  white-space: nowrap;
}

.value {
  margin:
    0 0 4px;

  font-size: 0.9rem;

  color: #374151;

  font-weight: 600;
}

.card small {
  color: #6b7280;

  font-size: 0.8rem;
}

.empty-column {
  padding:
    14px 8px;

  text-align: center;

  color: #94a3b8;

  font-size: 13px;
}

@media (max-width: 1000px) {
  .kanban {
    grid-template-columns:
      repeat(
        3,
        minmax(260px, 1fr)
      );
  }
}
</style>