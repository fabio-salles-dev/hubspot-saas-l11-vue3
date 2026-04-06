<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from "vue";
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

// ================== GROWTH ==================
const calculateGrowthData = (arr) => {
  if (!arr || arr.length < 2) return arr || [0];

  return arr.map((value, index) => {
    if (index === 0) return 0;
    return value - arr[index - 1];
  });
};

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
          borderWidth: 0,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: "bottom" },
      },
    },
  });
};

// ================== HISTORY ==================
const renderHistory = async () => {
  if (!historyCanvas.value) return;

  await nextTick();
  historyChart?.destroy();

  const hasData = props.history?.length > 0;

  const labels = hasData
    ? props.history.map((h) =>
        new Date(h.snapshot_date).toLocaleDateString("pt-BR")
      )
    : ["Sem dados"];

  const contacts = hasData ? props.history.map((h) => h.contacts) : [0];
  const companies = hasData ? props.history.map((h) => h.companies) : [0];
  const deals = hasData ? props.history.map((h) => h.deals) : [0];

  const contactsGrowth = calculateGrowthData(contacts);
  const companiesGrowth = calculateGrowthData(companies);
  const dealsGrowth = calculateGrowthData(deals);

  historyChart = new Chart(historyCanvas.value, {
    type: "line",
    data: {
      labels,
      datasets: [
        {
          label: "Contatos (crescimento)",
          data: contactsGrowth,
          tension: 0.4,
          borderWidth: 2,
        },
        {
          label: "Empresas (crescimento)",
          data: companiesGrowth,
          tension: 0.4,
          borderWidth: 2,
        },
        {
          label: "Negócios (crescimento)",
          data: dealsGrowth,
          tension: 0.4,
          borderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: "index",
        intersect: false,
      },
      plugins: {
        legend: { position: "top" },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { precision: 0 },
        },
      },
    },
  });
};

// ================== WATCH ==================
watch(() => props.overview, renderMetrics, { deep: true });
watch(() => props.history, renderHistory, { deep: true });

// ================== AUTO REFRESH ==================
let interval = null;

onMounted(() => {
  renderMetrics();
  renderHistory();

  interval = setInterval(() => {
    window.dispatchEvent(new Event("refresh-dashboard"));
  }, 15000); // 15 segundos
});

onBeforeUnmount(() => {
  metricsChart?.destroy();
  historyChart?.destroy();
  clearInterval(interval);
});
</script>

<template>
  <div class="charts-container">

    <!-- MÉTRICAS -->
    <div class="chart-card">
      <h3>Visão Geral</h3>
      <div class="chart-wrapper">
        <canvas ref="metricsCanvas"></canvas>
      </div>
    </div>

    <!-- HISTÓRICO -->
    <div class="chart-card">
      <h3>Histórico (Últimos 30 dias)</h3>

      <div class="chart-wrapper">
        <!-- Canvas SEMPRE existe -->
        <canvas ref="historyCanvas"></canvas>

        <!-- Overlay se vazio -->
        <div v-if="!history?.length" class="empty-overlay">
          <p>📭 Sem histórico disponível</p>
        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
.charts-container {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
  padding: 10px;
}

.chart-card {
  flex: 1 1 48%;
  min-height: 380px;
  background: #ffffff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
}

.chart-card h3 {
  margin-bottom: 20px;
  font-size: 1.1rem;
  color: #333;
}

.chart-wrapper {
  flex: 1;
  position: relative;
}

.empty-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f9fafb;
  border-radius: 8px;
  border: 1px dashed #ddd;
  color: #888;
  text-align: center;
}

@media (max-width: 768px) {
  .chart-card {
    flex: 1 1 100%;
  }
}
</style>