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

// Renderização do Gráfico de Rosca (Overview)
const renderMetrics = async () => {
  if (!props.overview?.objects || !metricsCanvas.value) return;
  
  await nextTick();
  metricsChart?.destroy();

  metricsChart = new Chart(metricsCanvas.value, {
    type: "doughnut",
    data: {
      labels: ["Contatos", "Empresas", "Negócios"],
      datasets: [{
        data: [
          props.overview.objects.contacts || 0,
          props.overview.objects.companies || 0,
          props.overview.objects.deals || 0,
        ],
        backgroundColor: ["#3b82f6", "#10b981", "#f59e0b"],
        borderWidth: 0,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom' }
      }
    },
  });
};

// Renderização do Gráfico de Linha (Histórico)
const renderHistory = async () => {
  if (!historyCanvas.value) return;
  
  await nextTick();
  historyChart?.destroy();

  // Só cria o gráfico se houver dados
  if (props.history?.length > 0) {
    historyChart = new Chart(historyCanvas.value, {
      type: "line",
      data: {
        labels: props.history.map((h) => h.snapshot_date),
        datasets: [
          { 
            label: "Contatos", 
            data: props.history.map((h) => h.contacts), 
            borderColor: "#3b82f6", 
            tension: 0.3,
            fill: false 
          },
          { 
            label: "Empresas", 
            data: props.history.map((h) => h.companies), 
            borderColor: "#10b981", 
            tension: 0.3,
            fill: false 
          },
          { 
            label: "Negócios", 
            data: props.history.map((h) => h.deals), 
            borderColor: "#f59e0b", 
            tension: 0.3, 
            fill: false 
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: { beginAtZero: true }
        }
      },
    });
  }
};

// Observar mudanças nos dados
watch(() => props.overview, renderMetrics, { deep: true });
watch(() => props.history, renderHistory, { deep: true });

onMounted(() => {
  renderMetrics();
  renderHistory();
});

// Importante: evita vazamento de memória
onBeforeUnmount(() => {
  metricsChart?.destroy();
  historyChart?.destroy();
});
</script>

<template>
  <div class="charts-container">
    <!-- Card Visão Geral -->
    <div class="chart-card">
      <h3>Visão Geral</h3>
      <div class="chart-wrapper">
        <canvas ref="metricsCanvas"></canvas>
      </div>
    </div>

    <!-- Card Histórico -->
    <div class="chart-card">
      <h3>Histórico (Últimos 30 dias)</h3>
      <div class="chart-wrapper">
        <!-- O canvas precisa estar no DOM mesmo sem dados para o ref funcionar -->
        <canvas v-show="history?.length" ref="historyCanvas"></canvas>
        
        <!-- Overlay de estado vazio -->
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
  flex: 1 1 400px; /* Cresce e diminui conforme a tela */
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
  font-family: sans-serif;
}

.chart-wrapper {
  flex: 1;
  position: relative; /* OBRIGATÓRIO para Chart.js responsivo */
  width: 100%;
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
}

@media (max-width: 768px) {
  .chart-card {
    flex: 1 1 100%;
  }
}
</style>
