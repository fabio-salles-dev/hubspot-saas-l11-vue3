<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from "vue";
import draggable from "vuedraggable";
import api from "@/services/api";

const props = defineProps({
  deals: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["updated"]);

// ==========================
// COLUMNS
// ==========================

const columns = ref({
  open: [],
  won: [],
  lost: [],
});

const mapDealsToColumns = () => {
  const deals = props.deals || [];

  columns.value.open = deals.filter(
    (deal) => deal.status === "open"
  );

  columns.value.won = deals.filter(
    (deal) => deal.status === "won"
  );

  columns.value.lost = deals.filter(
    (deal) => deal.status === "lost"
  );
};

watch(
  () => props.deals,
  mapDealsToColumns,
  {
    immediate: true,
    deep: true,
  }
);

// ==========================
// UPDATE STATUS
// ==========================

const updateDealStatus = async (deal, status) => {
  console.log("🔥 UPDATE DEAL");
  console.log("Deal:", deal);
  console.log("ID:", deal?.id);
  console.log("Status atual:", deal?.status);
  console.log("Novo status:", status);

  if (!deal?.id) {
    console.error("❌ Deal inválido:", deal);
    return;
  }

  try {
    const response = await api.put(`/deals/${deal.id}`, {
      status: status,
    });

    console.log("✅ PUT realizado com sucesso");
    console.log("Resposta:", response.data);

    // Recarrega os dados do Dashboard
    emit("updated");

  } catch (error) {
    console.error("❌ Erro ao atualizar deal:", error);
    console.error(
      "Resposta da API:",
      error.response?.data
    );
  }
};

// ==========================
// CHANGE / DROP
// ==========================

const onChange = async (event, status) => {
  console.log("=================================");
  console.log("🔥 CHANGE NO KANBAN");
  console.log("Status destino:", status);
  console.log("Evento:", event);
  console.log("=================================");

  // Só queremos tratar quando o deal
  // entrou em uma nova coluna.
  if (!event.added) {
    console.log("ℹ️ Nenhum deal foi adicionado nesta coluna.");
    return;
  }

  const deal = event.added.element;

  console.log("🔥 DEAL ARRASTADO");
  console.log("Deal:", deal);
  console.log("ID:", deal?.id);
  console.log("Título:", deal?.title);
  console.log("Status antigo:", deal?.status);
  console.log("Status novo:", status);

  if (!deal?.id) {
    console.error("❌ Deal sem ID.");
    return;
  }

  // Atualiza o objeto local imediatamente.
  deal.status = status;

  await updateDealStatus(deal, status);
};

// ==========================
// REALTIME
// ==========================

const handleDealUpdated = (event) => {
  console.log("🔥 REALTIME - DEAL ATUALIZADO");
  console.log("Evento:", event);

  // Pede ao Dashboard para recarregar
  // os negócios vindos do banco.
  emit("updated");
};

onMounted(() => {
  console.log("🔥 DealsKanban montado");

  if (window.Echo) {
    console.log("🔥 Conectando ao canal realtime: deals");

    window.Echo
      .channel("deals")
      .listen(".deal.updated", handleDealUpdated);
  } else {
    console.warn("⚠️ window.Echo não está disponível");
  }
});

onBeforeUnmount(() => {
  console.log("🧹 Desmontando DealsKanban");

  if (window.Echo) {
    window.Echo
      .channel("deals")
      .stopListening(".deal.updated", handleDealUpdated);
  }
});
</script>

<template>
  <div class="kanban">

    <!-- ==========================
         OPEN
    =========================== -->

    <div class="column">

      <h3 style="color: orange;">
        🟡 Abertos
      </h3>

      <draggable
        :list="columns.open"
        group="deals"
        item-key="id"
        @change="(event) => onChange(event, 'open')"
      >
        <template #item="{ element }">

          <div class="card">

            <h4>
              {{ element.title }}
            </h4>

            <p>
              💰 R$ {{ element.value }}
            </p>

            <small>
              {{ element.client?.name }}
            </small>

          </div>

        </template>
      </draggable>

    </div>


    <!-- ==========================
         WON
    =========================== -->

    <div class="column">

      <h3 style="color: green;">
        🟢 Ganhos
      </h3>

      <draggable
        :list="columns.won"
        group="deals"
        item-key="id"
        @change="(event) => onChange(event, 'won')"
      >
        <template #item="{ element }">

          <div class="card success">

            <h4>
              {{ element.title }}
            </h4>

            <p>
              💰 R$ {{ element.value }}
            </p>

            <small>
              {{ element.client?.name }}
            </small>

          </div>

        </template>
      </draggable>

    </div>


    <!-- ==========================
         LOST
    =========================== -->

    <div class="column">

      <h3 style="color: red;">
        🔴 Perdidos
      </h3>

      <draggable
        :list="columns.lost"
        group="deals"
        item-key="id"
        @change="(event) => onChange(event, 'lost')"
      >
        <template #item="{ element }">

          <div class="card danger">

            <h4>
              {{ element.title }}
            </h4>

            <p>
              💰 R$ {{ element.value }}
            </p>

            <small>
              {{ element.client?.name }}
            </small>

          </div>

        </template>
      </draggable>

    </div>

  </div>
</template>

<style scoped>

.kanban {
  display: flex;
  gap: 2px;
  overflow-x: auto;
  padding: auto;
  border-radius: 12px;
  border: 1px solid #2453b1;
  margin: 12px 0px 12px 0px;
}

.column {
  flex: 1;
  min-width: 280px;
  background: #cfe3f8;
  border-radius: 12px;
  padding: 12px;
}

.column h3 {
  margin-bottom: 10px;
  font-size: 1rem;
}

.card {
  background: white;
  padding: 12px;
  border-radius: 10px;
  margin-bottom: 10px;
  cursor: grab;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.card:active {
  cursor: grabbing;
}

.success {
  border-left: 4px solid #10b981;
}

.danger {
  border-left: 4px solid #ef4444;
}

h4 {
  margin: 0;
  font-size: 1rem;
  color: #1e3a8a;
  background: #f1f5f9;
  padding: 4px 8px;
  border-radius: 6px;
  margin-bottom: 4px;
}

p {
  margin: 0;
  font-size: 0.9rem;
  color: #374151;
}

small {
  color: #6b7280;
  font-size: 0.8rem;
}

</style>