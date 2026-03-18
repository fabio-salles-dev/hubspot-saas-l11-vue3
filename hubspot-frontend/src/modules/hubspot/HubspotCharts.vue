<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps({
    overview: Object,
    history: Array
})

const metricsCanvas = ref(null)
const historyCanvas = ref(null)
let metricsChart = null
let historyChart = null

const renderMetrics = async () => {
    if (!props.overview) return
    await nextTick()

    metricsChart?.destroy()
    metricsChart = new Chart(metricsCanvas.value, {
        type: 'doughnut',
        data: {
            labels: ['Contatos', 'Empresas', 'Negócios'],
            datasets: [{
                data: [
                    props.overview.objects.contacts,
                    props.overview.objects.companies,
                    props.overview.objects.deals
                ],
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // 🔑
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    })
}
const renderHistory = async () => {
    if (!props.history?.length) return
    await nextTick()

    historyChart?.destroy()
    historyChart = new Chart(historyCanvas.value, {
        type: 'line',
        data: {
            labels: props.history.map(h => h.snapshot_date),
            datasets: [
                {
                    label: 'Contatos',
                    data: props.history.map(h => h.contacts),
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Empresas',
                    data: props.history.map(h => h.companies),
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Negócios',
                    data: props.history.map(h => h.deals),
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // 🔑
            scales: {
                y: { beginAtZero: true }
            }
        }
    })
}
watch(() => props.overview, renderMetrics)
watch(() => props.history, renderHistory)

onMounted(() => {
    renderMetrics()
    renderHistory()
})
</script>

<template>
    <div class="charts">
        <div class="chart-wrapper">
            <canvas ref="metricsCanvas"></canvas>
        </div>

        <div class="chart-wrapper">
            <canvas ref="historyCanvas"></canvas>
        </div>
    </div>
</template>
<style scoped>
.charts {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    padding-bottom: 20px;
}

.chart-wrapper {
    flex: 1 1 45%;
    height: 350px;
    /* 🔑 CONTROLE DE ALTURA */
    position: relative;
    background: #eaeeb1;
    border-radius: 12px;
    padding: 18px;
}

.chart-wrapper canvas {
    width: 100% !important;
    height: 100% !important;
}

@media (max-width: 1024px) {
    .chart-wrapper {
        flex: 1 1 100%;
        height: 280px;
    }
}
</style>
