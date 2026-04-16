<script setup>
import { computed } from "vue";
import draggable from "vuedraggable";
import api from "@/services/api";

const props = defineProps({
  deals: Array,
});

const emit = defineEmits(["updated"]);

// ===== columns =====
const columns = ref({
  open: [],
  won: [],
  lost: [],
});

const mapDealsToColumns = () => {
  columns.value.open = props.deals.filter(d => d.status === "open");
  columns.value.won = props.deals.filter(d => d.status === "won");
  columns.value.lost = props.deals.filter(d => d.status === "lost");
};

watch(() => props.deals, mapDealsToColumns, { immediate: true });

// ===== UPDATE STATUS =====
const updateDealStatus = async (deal, status) => {
  try {
    await api.put(`/deals/${deal.id}`, { status });
    emit("updated");
  } catch (e) {
    console.error("Erro ao atualizar deal:", e);
  }
};

// ===== DROP =====
const onDrop = async (event, status) => {
  const deal = event.item.__vueParentComponent.props.element;
  await updateDealStatus(deal, status);
};

onMounted(() => {
  window.Echo.channel("deals")
    .listen(".deal.updated", (e) => {
      console.log("🔥 realtime chegou", e);

      emit("updated"); // recarrega lista do backend
    });
});
</script>

<template>
  <div class="kanban">

    <!-- OPEN -->
    <div class="column">
      <h3>🟡 Abertos</h3>

      <draggable
        :list="columns.open"
        group="deals"
        item-key="id"
        @end="(e) => onDrop(e, 'open')"
      >
        <template #item="{ element }">
          <div class="card">
            <h4>{{ element.title }}</h4>
            <p>💰 R$ {{ element.value }}</p>
            <small>{{ element.client?.name }}</small>
          </div>
        </template>
      </draggable>
    </div>

    <!-- WON -->
    <div class="column">
      <h3>🟢 Ganhos</h3>

      <draggable
        :list="columns.won"
        group="deals"
        item-key="id"
        @end="(e) => onDrop(e, 'won')"
      >
        <template #item="{ element }">
          <div class="card success">
            <h4>{{ element.title }}</h4>
            <p>💰 R$ {{ element.value }}</p>
            <small>{{ element.client?.name }}</small>
          </div>
        </template>
      </draggable>
    </div>

    <!-- LOST -->
    <div class="column">
      <h3>🔴 Perdidos</h3>

      <draggable
        :list="columns.lost"
        group="deals"
        item-key="id"
        @end="(e) => onDrop(e, 'lost')"
      >
        <template #item="{ element }">
          <div class="card danger">
            <h4>{{ element.title }}</h4>
            <p>💰 R$ {{ element.value }}</p>
            <small>{{ element.client?.name }}</small>
          </div>
        </template>
      </draggable>
    </div>

  </div>
</template>

<style scoped>
.kanban {
  display: flex;
  gap: 20px;
  overflow-x: auto;
}

.column {
  flex: 1;
  min-width: 280px;
  background: #f1f5f9;
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
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.success {
  border-left: 4px solid #10b981;
}

.danger {
  border-left: 4px solid #ef4444;
}
</style>