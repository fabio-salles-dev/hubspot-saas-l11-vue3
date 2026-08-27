<script setup>
import {
  ref,
  onMounted,
  onBeforeUnmount,
  watch,
  nextTick,
} from "vue";

import {
  Chart,
  registerables,
} from "chart.js";

Chart.register(...registerables);

// =====================================================
// PROPS
// =====================================================

const props = defineProps({
  overview: {
    type: Object,
    default: null,
  },

  history: {
    type: Array,
    default: () => [],
  },
});

// =====================================================
// REFS
// =====================================================

const metricsCanvas = ref(null);
const historyCanvas = ref(null);

let metricsChart = null;
let historyChart = null;

// =====================================================
// DESTROY CHARTS
// =====================================================

const destroyMetricsChart = () => {
  if (metricsChart) {
    metricsChart.destroy();
    metricsChart = null;
  }
};

const destroyHistoryChart = () => {
  if (historyChart) {
    historyChart.destroy();
    historyChart = null;
  }
};

// =====================================================
// METRICS CHART
// =====================================================

const renderMetrics = async () => {
  await nextTick();

  if (!metricsCanvas.value) {
    console.warn("HubspotCharts: metricsCanvas não encontrado");
    return;
  }

  if (!props.overview?.objects) {
    destroyMetricsChart();
    return;
  }

  destroyMetricsChart();

  console.log("🔥 CRIANDO GRÁFICO DE MÉTRICAS");

console.log("Dados:", {
  contacts: props.overview.objects.contacts,
  companies: props.overview.objects.companies,
  deals: props.overview.objects.deals,
});

console.log("Canvas:", metricsCanvas.value);

  const contacts = Number(
    props.overview.objects.contacts ?? 0
  );

  const companies = Number(
    props.overview.objects.companies ?? 0
  );

  const deals = Number(
    props.overview.objects.deals ?? 0
  );

  metricsChart = new Chart(metricsCanvas.value, {
    type: "doughnut",

    data: {
      labels: [
        "Contatos",
        "Empresas",
        "Negócios",
      ],

      datasets: [
        {
          data: [
            contacts,
            companies,
            deals,
          ],

          backgroundColor: [
            "#3b82f6",
            "#10b981",
            "#f59e0b",
          ],

          borderColor: "#ffffff",
          borderWidth: 3,

          hoverOffset: 8,
        },
      ],
    },

    options: {
      responsive: true,
      maintainAspectRatio: false,

      cutout: "62%",

      plugins: {
        legend: {
          position: "bottom",

          labels: {
            padding: 20,
            usePointStyle: true,
            pointStyle: "circle",
          },
        },

        tooltip: {
          callbacks: {
            label(context) {
              const value = context.raw ?? 0;

              return ` ${context.label}: ${value}`;
            },
          },
        },
      },

      animation: {
        duration: 800,
      },
    },
  });
};

// =====================================================
// HISTORY
// =====================================================

const renderHistory = async () => {
  await nextTick();

  if (!historyCanvas.value) {
    console.warn("HubspotCharts: historyCanvas não encontrado");
    return;
  }

  destroyHistoryChart();

  const history = Array.isArray(props.history)
    ? props.history
    : [];

  // ---------------------------------------------------
  // SEM HISTÓRICO
  // ---------------------------------------------------

  if (!history.length) {
    return;
  }

  // ---------------------------------------------------
  // ORDENA POR DATA
  // ---------------------------------------------------

  const sortedHistory = [...history].sort(
    (a, b) =>
      new Date(a.snapshot_date) -
      new Date(b.snapshot_date)
  );

  const labels = sortedHistory.map((item) =>
    new Date(item.snapshot_date).toLocaleDateString(
      "pt-BR",
      {
        day: "2-digit",
        month: "2-digit",
      }
    )
  );

  const contacts = sortedHistory.map((item) =>
    Number(item.contacts ?? 0)
  );

  const companies = sortedHistory.map((item) =>
    Number(item.companies ?? 0)
  );

  const deals = sortedHistory.map((item) =>
    Number(item.deals ?? 0)
  );

  // ---------------------------------------------------
  // GRÁFICO
  // ---------------------------------------------------

  historyChart = new Chart(historyCanvas.value, {
    type: "line",

    data: {
      labels,

      datasets: [
        {
          label: "Contatos",

          data: contacts,

          borderColor: "#3b82f6",

          backgroundColor:
            "rgba(59,130,246,0.10)",

          borderWidth: 3,

          tension: 0.4,

          fill: true,

          pointRadius: 4,

          pointHoverRadius: 7,
        },

        {
          label: "Empresas",

          data: companies,

          borderColor: "#10b981",

          backgroundColor:
            "rgba(16,185,129,0.10)",

          borderWidth: 3,

          tension: 0.4,

          fill: true,

          pointRadius: 4,

          pointHoverRadius: 7,
        },

        {
          label: "Negócios",

          data: deals,

          borderColor: "#f59e0b",

          backgroundColor:
            "rgba(245,158,11,0.10)",

          borderWidth: 3,

          tension: 0.4,

          fill: true,

          pointRadius: 4,

          pointHoverRadius: 7,
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
        legend: {
          position: "top",

          labels: {
            usePointStyle: true,
            pointStyle: "circle",
            padding: 20,
          },
        },

        tooltip: {
          mode: "index",
          intersect: false,
        },
      },

      scales: {
        x: {
          grid: {
            display: false,
          },
        },

        y: {
          beginAtZero: true,

          ticks: {
            precision: 0,
          },

          grid: {
            color: "rgba(148,163,184,0.15)",
          },
        },
      },

      animation: {
        duration: 800,
      },
    },
  });
};

// =====================================================
// RENDER ALL
// =====================================================

const renderCharts = async () => {
  await nextTick();

  await renderMetrics();

  await renderHistory();
};

// =====================================================
// WATCH OVERVIEW
// =====================================================

watch(
  () => props.overview,
  async () => {
    await renderMetrics();
  },
  {
    deep: true,
  }
);

// =====================================================
// WATCH HISTORY
// =====================================================

watch(
  () => props.history,
  async () => {
    await renderHistory();
  },
  {
    deep: true,
  }
);

// =====================================================
// AUTO REFRESH EVENT
// =====================================================

onMounted(async () => {
  await nextTick();

  renderMetrics();
  renderHistory();
});

// =====================================================
// CLEANUP
// =====================================================

onBeforeUnmount(() => {
  destroyMetricsChart();
  destroyHistoryChart();

});
</script>

<template>
  <div class="charts-container">

    <!-- ========================================= -->
    <!-- VISÃO GERAL -->
    <!-- ========================================= -->

    <div class="chart-card">

      <div class="chart-header">
        <div>
          <h3>Visão Geral</h3>

          <p>
            Distribuição dos dados do CRM
          </p>
        </div>
      </div>

      <div class="chart-wrapper">

        <canvas
          ref="metricsCanvas"
        ></canvas>

      </div>

    </div>

    <!-- ========================================= -->
    <!-- HISTÓRICO -->
    <!-- ========================================= -->

    <div class="chart-card">

      <div class="chart-header">
        <div>
          <h3>Histórico</h3>

          <p>
            Evolução dos últimos 30 dias
          </p>
        </div>
      </div>

      <div class="chart-wrapper">

        <canvas
          v-if="history?.length"
          ref="historyCanvas"
        ></canvas>

        <div
          v-else
          class="empty-overlay"
        >
          <div class="empty-content">

            <span class="empty-icon">
              📭
            </span>

            <strong>
              Sem histórico disponível
            </strong>

            <small>
              Novos snapshots aparecerão aqui
              conforme os dados forem atualizados.
            </small>

          </div>
        </div>

      </div>

    </div>

  </div>
</template>

<style scoped>

.charts-container {
  display: grid;

  grid-template-columns:
    repeat(2, minmax(0, 1fr));

  gap: 24px;

  width: 100%;
}

/* ================================================= */
/* CARD */
/* ================================================= */

.chart-card {
  min-width: 0;

  height: 390px;

  background: #ffffff;

  border-radius: 16px;

  padding: 24px;

  box-shadow:
    0 4px 14px rgba(15, 23, 42, 0.06);

  border:
    1px solid #e2e8f0;

  display: flex;

  flex-direction: column;
}

/* ================================================= */
/* HEADER */
/* ================================================= */

.chart-header {
  display: flex;

  justify-content: space-between;

  align-items: flex-start;

  margin-bottom: 16px;
}

.chart-header h3 {
  margin: 0;

  color: #0f172a;

  font-size: 1.15rem;

  font-weight: 700;
}

.chart-header p {
  margin: 5px 0 0;

  color: #64748b;

  font-size: 0.85rem;
}

/* ================================================= */
/* CANVAS */
/* ================================================= */

.chart-wrapper {
  position: relative;

  flex: 1;

  min-height: 0;

  width: 100%;
}

.chart-wrapper canvas {
  width: 100% !important;

  height: 100% !important;

  display: block;
}

/* ================================================= */
/* EMPTY */
/* ================================================= */

.empty-overlay {
  position: absolute;

  inset: 0;

  display: flex;

  align-items: center;

  justify-content: center;

  background: #f8fafc;

  border-radius: 12px;

  border: 1px dashed #cbd5e1;
}

.empty-content {
  display: flex;

  flex-direction: column;

  align-items: center;

  text-align: center;

  gap: 8px;

  padding: 20px;

  color: #64748b;
}

.empty-icon {
  font-size: 32px;

  margin-bottom: 4px;
}

.empty-content strong {
  color: #475569;

  font-size: 0.95rem;
}

.empty-content small {
  max-width: 280px;

  line-height: 1.5;

  font-size: 0.8rem;

  color: #94a3b8;
}

/* ================================================= */
/* RESPONSIVO */
/* ================================================= */

@media (max-width: 900px) {

  .charts-container {
    grid-template-columns: 1fr;
  }

}

@media (max-width: 768px) {

  .chart-card {
    height: 350px;

    padding: 18px;
  }

}

</style>