<script setup>
import {
  ref,
  watch,
  nextTick,
  onMounted,
  onBeforeUnmount
} from "vue";

import {
  Chart,
  registerables
} from "chart.js";

Chart.register(...registerables);

// ============================================================
// PROPS
// ============================================================

const props = defineProps({
  overview: {
    type: Object,
    default: null
  },

  history: {
    type: Array,
    default: () => []
  },

  dealSummary: {
    type: Object,
    default: null
  }
});

// ============================================================
// CANVAS REFS
// ============================================================

const metricsCanvas = ref(null);
const historyCanvas = ref(null);
const stagesCanvas = ref(null);
const statusCanvas = ref(null);

// ============================================================
// CHART INSTANCES
// ============================================================

let metricsChart = null;
let historyChart = null;
let stagesChart = null;
let statusChart = null;

// ============================================================
// FORMATTERS
// ============================================================

const formatNumber = (value) => {
  return new Intl.NumberFormat(
    "pt-BR"
  ).format(
    Number(value) || 0
  );
};

const formatDate = (value) => {
  if (!value) {
    return "—";
  }

  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return date.toLocaleDateString(
    "pt-BR"
  );
};

// ============================================================
// HUBSPOT STAGES
// ============================================================

const getStageName = (stage) => {
  const stages = {
    appointmentscheduled: "Agendamento",
    qualifiedtobuy: "Qualificado",
    presentationscheduled: "Apresentação",
    decisionmakerboughtin: "Decisor envolvido",
    contractsent: "Contrato enviado",
    closedwon: "Ganho",
    closedlost: "Perdido"
  };

  return (
    stages[stage]
    || stage
    || "Sem etapa"
  );
};

// ============================================================
// DESTROY CHART
// ============================================================

const destroyChart = (chart) => {
  if (chart) {
    chart.destroy();
  }

  return null;
};

const destroyCharts = () => {
  metricsChart =
    destroyChart(metricsChart);

  historyChart =
    destroyChart(historyChart);

  stagesChart =
    destroyChart(stagesChart);

  statusChart =
    destroyChart(statusChart);
};

// ============================================================
// METRICS CHART
// ============================================================

const renderMetricsChart = () => {
  if (
    !metricsCanvas.value
    || !props.overview
  ) {
    metricsChart =
      destroyChart(metricsChart);

    return;
  }

  metricsChart =
    destroyChart(metricsChart);

  const contacts =
    Number(
      props.overview.objects?.contacts
    ) || 0;

  const companies =
    Number(
      props.overview.objects?.companies
    ) || 0;

  const deals =
    Number(
      props.overview.objects?.deals
    ) || 0;

  metricsChart = new Chart(
    metricsCanvas.value,
    {
      type: "doughnut",

      data: {
        labels: [
          "Contatos",
          "Empresas",
          "Negócios"
        ],

        datasets: [
          {
            data: [
              contacts,
              companies,
              deals
            ],

            backgroundColor: [
              "#3b82f6",
              "#10b981",
              "#f59e0b"
            ],

            borderColor: "#ffffff",
            borderWidth: 3,
            hoverOffset: 10
          }
        ]
      },

      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: "62%",

        animation: {
          duration: 800
        },

        plugins: {
          legend: {
            position: "bottom",

            labels: {
              padding: 18,
              usePointStyle: true,
              pointStyle: "circle"
            }
          },

          tooltip: {
            callbacks: {
              label(context) {
                const value =
                  context.raw ?? 0;

                return (
                  ` ${context.label}: `
                  + formatNumber(value)
                );
              }
            }
          }
        }
      }
    }
  );
};

// ============================================================
// HISTORY CHART
// ============================================================

const renderHistoryChart = () => {
  if (
    !historyCanvas.value
    || !Array.isArray(props.history)
    || !props.history.length
  ) {
    historyChart =
      destroyChart(historyChart);

    return;
  }

  historyChart =
    destroyChart(historyChart);

  const sortedHistory =
    [...props.history].sort(
      (a, b) =>
        new Date(a.snapshot_date)
        - new Date(b.snapshot_date)
    );

  const labels =
    sortedHistory.map(
      item =>
        formatDate(
          item.snapshot_date
        )
    );

  const contacts =
    sortedHistory.map(
      item =>
        Number(item.contacts) || 0
    );

  const companies =
    sortedHistory.map(
      item =>
        Number(item.companies) || 0
    );

  const deals =
    sortedHistory.map(
      item =>
        Number(item.deals) || 0
    );

  historyChart = new Chart(
    historyCanvas.value,
    {
      type: "line",

      data: {
        labels,

        datasets: [
          {
            label: "Contatos",
            data: contacts,

            borderColor:
              "#3b82f6",

            backgroundColor:
              "rgba(59,130,246,0.12)",

            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointHoverRadius: 7
          },

          {
            label: "Empresas",
            data: companies,

            borderColor:
              "#10b981",

            backgroundColor:
              "rgba(16,185,129,0.12)",

            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointHoverRadius: 7
          },

          {
            label: "Negócios",
            data: deals,

            borderColor:
              "#f59e0b",

            backgroundColor:
              "rgba(245,158,11,0.12)",

            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointHoverRadius: 7
          }
        ]
      },

      options: {
        responsive: true,
        maintainAspectRatio: false,

        interaction: {
          mode: "index",
          intersect: false
        },

        animation: {
          duration: 800
        },

        plugins: {
          legend: {
            position: "top",

            labels: {
              usePointStyle: true,
              pointStyle: "circle",
              padding: 18
            }
          },

          tooltip: {
            mode: "index",
            intersect: false
          }
        },

        scales: {
          x: {
            grid: {
              display: false
            }
          },

          y: {
            beginAtZero: true,

            ticks: {
              precision: 0
            },

            grid: {
              color:
                "rgba(148,163,184,0.15)"
            }
          }
        }
      }
    }
  );
};

// ============================================================
// STAGES CHART
// ============================================================

const renderStagesChart = () => {
  if (
    !stagesCanvas.value
    || !props.dealSummary
  ) {
    stagesChart =
      destroyChart(stagesChart);

    return;
  }

  stagesChart =
    destroyChart(stagesChart);

  const byStage =
    props.dealSummary.by_stage
    || {};

  const stages =
    Object.keys(byStage);

  if (!stages.length) {
    return;
  }

  stagesChart = new Chart(
    stagesCanvas.value,
    {
      type: "bar",

      data: {
        labels:
          stages.map(
            stage =>
              getStageName(stage)
          ),

        datasets: [
          {
            label: "Negócios",

            data:
              stages.map(
                stage =>
                  Number(
                    byStage[
                      stage
                    ]?.count
                  ) || 0
              ),

            backgroundColor:
              "#ff7a59",

            borderRadius: 6
          }
        ]
      },

      options: {
        responsive: true,
        maintainAspectRatio: false,

        indexAxis: "y",

        animation: {
          duration: 800
        },

        plugins: {
          legend: {
            display: false
          }
        },

        scales: {
          x: {
            beginAtZero: true,

            ticks: {
              precision: 0
            }
          },

          y: {
            grid: {
              display: false
            }
          }
        }
      }
    }
  );
};

// ============================================================
// STATUS CHART
// ============================================================

const renderStatusChart = () => {
  if (
    !statusCanvas.value
    || !props.dealSummary
  ) {
    statusChart =
      destroyChart(statusChart);

    return;
  }

  statusChart =
    destroyChart(statusChart);

  const won =
    Number(
      props.dealSummary.won
    ) || 0;

  const lost =
    Number(
      props.dealSummary.lost
    ) || 0;

  const open =
    Number(
      props.dealSummary.open
    ) || 0;

  statusChart = new Chart(
    statusCanvas.value,
    {
      type: "doughnut",

      data: {
        labels: [
          "Ganhos",
          "Perdidos",
          "Em aberto"
        ],

        datasets: [
          {
            data: [
              won,
              lost,
              open
            ],

            backgroundColor: [
              "#10b981",
              "#ef4444",
              "#3b82f6"
            ],

            borderColor: "#ffffff",
            borderWidth: 3,
            hoverOffset: 10
          }
        ]
      },

      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: "60%",

        animation: {
          duration: 800
        },

        plugins: {
          legend: {
            position: "bottom",

            labels: {
              padding: 18,
              usePointStyle: true,
              pointStyle: "circle"
            }
          },

          tooltip: {
            callbacks: {
              label(context) {
                const value =
                  context.raw ?? 0;

                return (
                  ` ${context.label}: `
                  + formatNumber(value)
                );
              }
            }
          }
        }
      }
    }
  );
};

// ============================================================
// RENDER ALL
// ============================================================

const renderCharts = async () => {
  await nextTick();

  renderMetricsChart();
  renderStatusChart();
  renderStagesChart();
  renderHistoryChart();
};

// ============================================================
// WATCH PROPS
// ============================================================

watch(
  () => [
    props.overview,
    props.history,
    props.dealSummary
  ],
  async () => {
    await renderCharts();
  },
  {
    deep: true
  }
);

// ============================================================
// LIFECYCLE
// ============================================================

onMounted(async () => {
  await renderCharts();
});

onBeforeUnmount(() => {
  destroyCharts();
});
</script>

<template>
  <div class="charts-grid">

    <!-- ================================================== -->
    <!-- DISTRIBUIÇÃO -->
    <!-- ================================================== -->

    <div class="chart-card">
      <div class="chart-header">
        <h3>
          Distribuição da conta
        </h3>

        <span>
          Contatos × Empresas × Negócios
        </span>
      </div>

      <div class="chart-wrapper">
        <canvas
          ref="metricsCanvas"
        ></canvas>
      </div>
    </div>

    <!-- ================================================== -->
    <!-- STATUS -->
    <!-- ================================================== -->

    <div class="chart-card">
      <div class="chart-header">
        <h3>
          Status dos negócios
        </h3>

        <span>
          Ganhos × Perdidos × Abertos
        </span>
      </div>

      <div class="chart-wrapper">
        <canvas
          ref="statusCanvas"
        ></canvas>
      </div>
    </div>

    <!-- ================================================== -->
    <!-- ETAPAS -->
    <!-- ================================================== -->

    <div class="chart-card wide">
      <div class="chart-header">
        <h3>
          Negócios por etapa
        </h3>

        <span>
          Distribuição atual do pipeline
        </span>
      </div>

      <div
        class="chart-wrapper stages-wrapper"
      >
        <canvas
          ref="stagesCanvas"
        ></canvas>
      </div>
    </div>

    <!-- ================================================== -->
    <!-- HISTÓRICO -->
    <!-- ================================================== -->

    <div
      v-if="history.length"
      class="chart-card wide"
    >
      <div class="chart-header">
        <h3>
          Histórico da conta
        </h3>

        <span>
          Evolução dos objetos
        </span>
      </div>

      <div class="chart-wrapper">
        <canvas
          ref="historyCanvas"
        ></canvas>
      </div>
    </div>

  </div>
</template>

<style scoped>
.charts-grid {
  display: grid;
  grid-template-columns:
    repeat(2, minmax(0, 1fr));
  gap: 20px;
}

.chart-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 20px;

  box-shadow:
    0 4px 12px
    rgba(15, 23, 42, 0.04);

  min-width: 0;
}

.chart-card.wide {
  grid-column: span 2;
}

.chart-header {
  margin-bottom: 14px;
}

.chart-header h3 {
  margin: 0 0 5px;
  color: #33475b;
  font-size: 17px;
}

.chart-header span {
  font-size: 13px;
  color: #94a3b8;
}

.chart-wrapper {
  position: relative;

  width: 100%;
  height: 300px;
  min-height: 300px;
}

.stages-wrapper {
  height: 350px;
  min-height: 350px;
}

.chart-wrapper canvas {
  display: block;

  width: 100% !important;
  height: 100% !important;
}

@media (max-width: 768px) {
  .charts-grid {
    grid-template-columns: 1fr;
  }

  .chart-card.wide {
    grid-column: span 1;
  }
}
</style>