<script setup>
import { ref, onMounted, watch, nextTick } from "vue";
import { Chart, registerables } from "chart.js";

Chart.register(...registerables);

const props = defineProps({
  overview: Object,
  history: Array,
});

const metricsCanvas = ref(null);
const historyCanvas = ref(null);

let metricsChart = null;
let historyChart = null;

// ================== METRICS ==================
const renderMetrics = async () => {
  if (!props.overview?.objects || !metricsCanvas.value) return;

  await nextTick();

  metricsChart?.destroy();

  metricsChart = new Chart(metricsCanvas.value, {
    type: "doughnut",
    data: {
      labels: ["Contatos", "Empresas", "Negócios"],
      datasets: [
        {
          data: [
            props.overview.objects.contacts || 0,
            props.overview.objects.companies || 0,
            props.overview.objects.deals || 0,
          ],
          backgroundColor: ["#3b82f6", "#10b981", "#f59e0b"],
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
    },
  });
};

// ================== HISTORY (CORRETO) ==================
const renderHistory = async () => {
  if (!props.history?.length) return;
  if (!historyCanvas.value) return;

  await nextTick();

  historyChart?.destroy();

  historyChart = new Chart(historyCanvas.value, {
    type: "line",
    data: {
      labels: props.history.map((h) => h.snapshot_date),
      datasets: [
        {
          label: "Contatos",
          data: props.history.map((h) => h.contacts),
        },
        {
          label: "Empresas",
          data: props.history.map((h) => h.companies),
        },
        {
          label: "Negócios",
          data: props.history.map((h) => h.deals),
        },
      ],
    },
  });
};

// ================== WATCH ==================
watch(() => props.overview, renderMetrics, { immediate: true, deep: true });
watch(() => props.history, renderHistory, { immediate: true, deep: true });

// ================== INIT ==================
onMounted(() => {
  renderMetrics();
  renderHistory();
});
</script>
<template>
  <div class="charts-container">
    <!-- MÉTRICAS (sempre aparece) -->
    <div class="chart-card">
      <h3>Visão Geral</h3>
      <canvas ref="metricsCanvas"></canvas>
    </div>

    <!-- HISTÓRICO (sempre ocupa espaço) -->
    <div class="chart-card">
      <h3>Histórico (Últimos 30 dias)</h3>

      <canvas ref="historyCanvas"></canvas>

      <!-- overlay -->
      <div v-if="!history.length" class="empty-overlay">
        📭 Sem histórico ainda
      </div>
    </div>
  </div>
</template>

<style scoped>
.chart-card {
  height: 350px;
}

canvas {
  width: 100% !important;
  height: 100% !important;
  display: block;
}
.empty-state {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  opacity: 0.6;
  text-align: center;
}

.chart-card {
  height: 350px;
  background: #fff;
  border-radius: 12px;
  padding: 16px;
}
.charts-container {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

.chart-card {
  flex: 1 1 48%;
  height: 350px;
  background: #fff;
  border-radius: 12px;
  padding: 16px;
}

/* Mobile */
@media (max-width: 768px) {
  .chart-card {
    flex: 1 1 100%;
  }
}
</style>
