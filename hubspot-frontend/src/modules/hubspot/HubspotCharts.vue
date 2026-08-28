```vue
<script setup>
import {
  ref,
  onMounted,
  onBeforeUnmount,
  nextTick
} from 'vue'

import {
  Chart,
  registerables
} from 'chart.js'

import api from '@/services/api'
import echo from '@/services/echo'

Chart.register(...registerables)

// ============================================================
// REFS
// ============================================================

const connected = ref(false)
const loading = ref(true)
const importing = ref(false)
const refreshing = ref(false)

const error = ref(null)

const account = ref(null)
const overview = ref(null)
const history = ref([])

const dealSummary = ref(null)

const animatedContacts = ref(0)
const animatedCompanies = ref(0)
const animatedDeals = ref(0)

const metricsCanvas = ref(null)
const historyCanvas = ref(null)
const stagesCanvas = ref(null)
const statusCanvas = ref(null)

let metricsChart = null
let historyChart = null
let stagesChart = null
let statusChart = null

let echoChannel = null

const platformName = ref(
  'DevNest HubSpot Account'
)

// ============================================================
// ANIMAÇÃO DOS NÚMEROS
// ============================================================

const animateNumber = (targetRef, value) => {
  const numericValue = Number(value) || 0

  if (numericValue === 0) {
    targetRef.value = 0
    return
  }

  let start = 0

  const step =
    Math.max(
      Math.ceil(numericValue / 30),
      1
    )

  const interval = setInterval(() => {
    start += step

    if (start >= numericValue) {
      targetRef.value = numericValue
      clearInterval(interval)
    } else {
      targetRef.value = start
    }
  }, 20)
}

// ============================================================
// FORMATAÇÃO
// ============================================================

const formatCurrency = (value) => {
  const number = Number(value) || 0

  return new Intl.NumberFormat(
    'pt-BR',
    {
      style: 'currency',
      currency: 'BRL'
    }
  ).format(number)
}

const formatNumber = (value) => {
  return new Intl.NumberFormat(
    'pt-BR'
  ).format(
    Number(value) || 0
  )
}

const formatDate = (value) => {
  if (!value) {
    return '—'
  }

  const date = new Date(value)

  if (
    Number.isNaN(
      date.getTime()
    )
  ) {
    return value
  }

  return date.toLocaleDateString(
    'pt-BR'
  )
}

const getStageName = (stage) => {
  const stages = {
    appointmentscheduled:
      'Agendamento',

    qualifiedtobuy:
      'Qualificado',

    presentationscheduled:
      'Apresentação',

    decisionmakerboughtin:
      'Decisor envolvido',

    contractsent:
      'Contrato enviado',

    closedwon:
      'Ganho',

    closedlost:
      'Perdido'
  }

  return (
    stages[stage] ||
    stage ||
    'Sem etapa'
  )
}

// ============================================================
// CONECTAR
// ============================================================

const connect = () => {
  window.location.href =
    'http://localhost:8000/api/hubspot/redirect'
}

// ============================================================
// IMPORTAR CONTATOS
// ============================================================

const importContacts = async () => {
  importing.value = true
  error.value = null

  try {
    const { data } =
      await api.post(
        '/hubspot/import'
      )

    alert(
      `✅ ${
        data.imported ?? 0
      } contatos importados com sucesso`
    )

    await refreshDashboard()

  } catch (err) {
    console.error(
      'Erro ao importar contatos:',
      err
    )

    alert(
      '❌ Erro ao importar contatos'
    )

  } finally {
    importing.value = false
  }
}

// ============================================================
// DESCONECTAR
// ============================================================

const disconnect = async () => {
  if (
    !confirm(
      'Deseja desconectar a conta HubSpot?'
    )
  ) {
    return
  }

  try {
    await api.post(
      '/hubspot/disconnect'
    )

    connected.value = false
    account.value = null
    overview.value = null
    dealSummary.value = null
    history.value = []

    animatedContacts.value = 0
    animatedCompanies.value = 0
    animatedDeals.value = 0

    destroyCharts()

  } catch (err) {
    console.error(
      'Erro ao desconectar:',
      err
    )

    alert(
      'Erro ao desconectar'
    )
  }
}

// ============================================================
// DESTRUIR GRÁFICOS
// ============================================================

const destroyMetricsChart = () => {
  if (metricsChart) {
    metricsChart.destroy()
    metricsChart = null
  }
}

const destroyHistoryChart = () => {
  if (historyChart) {
    historyChart.destroy()
    historyChart = null
  }
}

const destroyStagesChart = () => {
  if (stagesChart) {
    stagesChart.destroy()
    stagesChart = null
  }
}

const destroyStatusChart = () => {
  if (statusChart) {
    statusChart.destroy()
    statusChart = null
  }
}

const destroyCharts = () => {
  destroyMetricsChart()
  destroyHistoryChart()
  destroyStagesChart()
  destroyStatusChart()
}

// ============================================================
// OVERVIEW
// ============================================================

const loadOverview = async () => {
  try {
    const { data } =
      await api.get(
        '/hubspot/overview'
      )

    if (!data) {
      overview.value = null
      return
    }

    overview.value = data

    animateNumber(
      animatedContacts,
      data.objects?.contacts ?? 0
    )

    animateNumber(
      animatedCompanies,
      data.objects?.companies ?? 0
    )

    animateNumber(
      animatedDeals,
      data.objects?.deals ?? 0
    )

  } catch (err) {
    console.error(
      'Erro ao carregar overview:',
      err
    )

    error.value =
      '❌ Não foi possível carregar os dados da conta'
  }
}

// ============================================================
// OVERVIEW LIVE
// ============================================================

const loadLiveOverview = async () => {
  try {
    const { data } =
      await api.get(
        '/hubspot/overview-live'
      )

    if (!data) {
      return
    }

    overview.value = data

    animateNumber(
      animatedContacts,
      data.objects?.contacts ?? 0
    )

    animateNumber(
      animatedCompanies,
      data.objects?.companies ?? 0
    )

    animateNumber(
      animatedDeals,
      data.objects?.deals ?? 0
    )

  } catch (err) {
    console.error(
      'Erro ao carregar overview live:',
      err
    )
  }
}

// ============================================================
// HISTÓRICO
// ============================================================

const loadHistory = async () => {
  try {
    const { data } =
      await api.get(
        '/hubspot/history'
      )

    history.value =
      Array.isArray(data)
        ? data
        : []

  } catch (err) {
    console.error(
      'Erro ao carregar histórico:',
      err
    )

    error.value =
      '❌ Não foi possível carregar o histórico'
  }
}

// ============================================================
// RESUMO DOS NEGÓCIOS
// ============================================================

const loadDealSummary = async () => {
  try {
    const { data } =
      await api.get(
        '/hubspot/deals/summary'
      )

    dealSummary.value =
      data

    console.log(
      '💼 RESUMO DOS NEGÓCIOS CARREGADO:',
      data
    )

  } catch (err) {
    console.error(
      'Erro ao carregar resumo dos negócios:',
      err
    )

    error.value =
      '❌ Não foi possível carregar os negócios'
  }
}

// ============================================================
// ATUALIZAR DASHBOARD
// ============================================================

const refreshDashboard = async () => {
  if (!connected.value) {
    return
  }

  refreshing.value = true
  error.value = null

  try {
    console.log(
      '🔄 Atualizando dashboard...'
    )

    await Promise.all([
      loadLiveOverview(),
      loadDealSummary(),
      loadHistory()
    ])

    console.log(
      '📦 Dados atualizados'
    )

    await nextTick()

    await nextTick()

    await renderAllCharts()

  } catch (err) {
    console.error(
      'Erro ao atualizar dashboard:',
      err
    )

    error.value =
      '❌ Não foi possível atualizar o dashboard'

  } finally {
    refreshing.value = false
  }
}

// ============================================================
// GRÁFICO DE MÉTRICAS
// ============================================================

const renderMetricsChart = async () => {
  await nextTick()

  console.log(
    '🔥 PREPARANDO GRÁFICO DE MÉTRICAS'
  )

  if (!overview.value) {
    console.warn(
      '⚠️ Overview ainda não disponível'
    )
    return
  }

  const canvas =
    metricsCanvas.value

  console.log(
    'Canvas de métricas:',
    canvas
  )

  if (!canvas) {
    console.warn(
      '⚠️ Canvas de métricas não disponível'
    )
    return
  }

  destroyMetricsChart()

  const contacts =
    Number(
      overview.value.objects?.contacts
    ) || 0

  const companies =
    Number(
      overview.value.objects?.companies
    ) || 0

  const deals =
    Number(
      overview.value.objects?.deals
    ) || 0

  console.log(
    '📊 Dados das métricas:',
    {
      contacts,
      companies,
      deals
    }
  )

  metricsChart =
    new Chart(
      canvas,
      {
        type: 'doughnut',

        data: {
          labels: [
            'Contatos',
            'Empresas',
            'Negócios'
          ],

          datasets: [
            {
              data: [
                contacts,
                companies,
                deals
              ],

              backgroundColor: [
                '#3b82f6',
                '#10b981',
                '#f59e0b'
              ],

              borderColor:
                '#ffffff',

              borderWidth: 3,

              hoverOffset: 10
            }
          ]
        },

        options: {
          responsive: true,

          maintainAspectRatio:
            false,

          cutout: '62%',

          animation: {
            duration: 800
          },

          plugins: {
            legend: {
              position:
                'bottom',

              labels: {
                padding: 18,

                usePointStyle:
                  true,

                pointStyle:
                  'circle'
              }
            },

            tooltip: {
              callbacks: {
                label(context) {
                  const value =
                    context.raw ?? 0

                  return (
                    ` ${context.label}: ` +
                    `${formatNumber(value)}`
                  )
                }
              }
            }
          }
        }
      }
    )

  console.log(
    '✅ GRÁFICO DE MÉTRICAS CRIADO'
  )
}

// ============================================================
// GRÁFICO DE HISTÓRICO
// ============================================================

const renderHistoryChart = async () => {
  await nextTick()

  console.log(
    '🔥 PREPARANDO GRÁFICO DE HISTÓRICO'
  )

  const canvas =
    historyCanvas.value

  console.log(
    'Canvas de histórico:',
    canvas
  )

  if (!canvas) {
    console.warn(
      '⚠️ Canvas de histórico não disponível'
    )
    return
  }

  destroyHistoryChart()

  if (
    !Array.isArray(
      history.value
    ) ||
    !history.value.length
  ) {
    console.warn(
      '⚠️ Histórico vazio'
    )
    return
  }

  const sortedHistory =
    [...history.value].sort(
      (a, b) =>
        new Date(
          a.snapshot_date
        ) -
        new Date(
          b.snapshot_date
        )
    )

  const labels =
    sortedHistory.map(
      item =>
        formatDate(
          item.snapshot_date
        )
    )

  const contacts =
    sortedHistory.map(
      item =>
        Number(
          item.contacts
        ) || 0
    )

  const companies =
    sortedHistory.map(
      item =>
        Number(
          item.companies
        ) || 0
    )

  const deals =
    sortedHistory.map(
      item =>
        Number(
          item.deals
        ) || 0
    )

  historyChart =
    new Chart(
      canvas,
      {
        type: 'line',

        data: {
          labels,

          datasets: [
            {
              label:
                'Contatos',

              data:
                contacts,

              borderColor:
                '#3b82f6',

              backgroundColor:
                'rgba(59,130,246,0.12)',

              borderWidth: 3,

              tension: 0.4,

              fill: true,

              pointRadius: 4,

              pointHoverRadius: 7
            },

            {
              label:
                'Empresas',

              data:
                companies,

              borderColor:
                '#10b981',

              backgroundColor:
                'rgba(16,185,129,0.12)',

              borderWidth: 3,

              tension: 0.4,

              fill: true,

              pointRadius: 4,

              pointHoverRadius: 7
            },

            {
              label:
                'Negócios',

              data:
                deals,

              borderColor:
                '#f59e0b',

              backgroundColor:
                'rgba(245,158,11,0.12)',

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

          maintainAspectRatio:
            false,

          interaction: {
            mode: 'index',
            intersect: false
          },

          animation: {
            duration: 800
          },

          plugins: {
            legend: {
              position: 'top',

              labels: {
                usePointStyle:
                  true,

                pointStyle:
                  'circle',

                padding: 18
              }
            },

            tooltip: {
              mode: 'index',

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
                  'rgba(148,163,184,0.15)'
              }
            }
          }
        }
      }
    )

  console.log(
    '✅ GRÁFICO DE HISTÓRICO CRIADO'
  )
}

// ============================================================
// GRÁFICO POR ETAPA
// ============================================================

const renderStagesChart = async () => {
  await nextTick()

  console.log(
    '🔥 PREPARANDO GRÁFICO DE ETAPAS'
  )

  const canvas =
    stagesCanvas.value

  console.log(
    'Canvas de etapas:',
    canvas
  )

  if (!canvas) {
    console.warn(
      '⚠️ Canvas de etapas não disponível'
    )
    return
  }

  if (!dealSummary.value) {
    console.warn(
      '⚠️ Deal Summary ainda não disponível'
    )
    return
  }

  destroyStagesChart()

  const byStage =
    dealSummary.value.by_stage ||
    {}

  const stages =
    Object.keys(byStage)

  console.log(
    '📊 Etapas recebidas:',
    byStage
  )

  if (!stages.length) {
    console.warn(
      '⚠️ Nenhuma etapa encontrada'
    )
    return
  }

  stagesChart =
    new Chart(
      canvas,
      {
        type: 'bar',

        data: {
          labels:
            stages.map(
              stage =>
                getStageName(
                  stage
                )
            ),

          datasets: [
            {
              label:
                'Negócios',

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
                '#ff7a59',

              borderRadius: 6
            }
          ]
        },

        options: {
          responsive: true,

          maintainAspectRatio:
            false,

          indexAxis: 'y',

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
    )

  console.log(
    '✅ GRÁFICO DE ETAPAS CRIADO'
  )
}

// ============================================================
// GRÁFICO DE STATUS
// ============================================================

const renderStatusChart = async () => {
  await nextTick()

  console.log(
    '🔥 PREPARANDO GRÁFICO DE STATUS'
  )

  const canvas =
    statusCanvas.value

  console.log(
    'Canvas de status:',
    canvas
  )

  if (!canvas) {
    console.warn(
      '⚠️ Canvas de status não disponível'
    )
    return
  }

  if (!dealSummary.value) {
    console.warn(
      '⚠️ Deal Summary ainda não disponível'
    )
    return
  }

  destroyStatusChart()

  const won =
    Number(
      dealSummary.value.won
    ) || 0

  const lost =
    Number(
      dealSummary.value.lost
    ) || 0

  const open =
    Number(
      dealSummary.value.open
    ) || 0

  console.log(
    '📊 Status recebidos:',
    {
      won,
      lost,
      open
    }
  )

  statusChart =
    new Chart(
      canvas,
      {
        type: 'doughnut',

        data: {
          labels: [
            'Ganhos',
            'Perdidos',
            'Em aberto'
          ],

          datasets: [
            {
              data: [
                won,
                lost,
                open
              ],

              backgroundColor: [
                '#10b981',
                '#ef4444',
                '#3b82f6'
              ],

              borderColor:
                '#ffffff',

              borderWidth: 3,

              hoverOffset: 10
            }
          ]
        },

        options: {
          responsive: true,

          maintainAspectRatio:
            false,

          cutout: '60%',

          animation: {
            duration: 800
          },

          plugins: {
            legend: {
              position:
                'bottom',

              labels: {
                padding: 18,

                usePointStyle:
                  true,

                pointStyle:
                  'circle'
              }
            },

            tooltip: {
              callbacks: {
                label(context) {
                  const value =
                    context.raw ?? 0

                  return (
                    ` ${context.label}: ` +
                    `${formatNumber(value)}`
                  )
                }
              }
            }
          }
        }
      }
    )

  console.log(
    '✅ GRÁFICO DE STATUS CRIADO'
  )
}

// ============================================================
// RENDERIZAR TODOS OS GRÁFICOS
// ============================================================

const renderAllCharts = async () => {

  console.log(
    '================================'
  )

  console.log(
    '📊 INICIANDO RENDERIZAÇÃO DOS GRÁFICOS'
  )

  console.log(
    'Overview:',
    overview.value
  )

  console.log(
    'Deal Summary:',
    dealSummary.value
  )

  console.log(
    'Histórico:',
    history.value
  )

  // ==========================================================
  // GARANTIR QUE O VUE TERMINOU O RENDER
  // ==========================================================

  await nextTick()

  await nextTick()

  // ==========================================================
  // VERIFICAR TODOS OS CANVAS
  // ==========================================================

  console.log(
    '================================'
  )

  console.log(
    '🔎 CANVAS DISPONÍVEIS'
  )

  console.log(
    'metricsCanvas:',
    metricsCanvas.value
  )

  console.log(
    'historyCanvas:',
    historyCanvas.value
  )

  console.log(
    'stagesCanvas:',
    stagesCanvas.value
  )

  console.log(
    'statusCanvas:',
    statusCanvas.value
  )

  console.log(
    '================================'
  )

  // ==========================================================
  // GRÁFICO DE MÉTRICAS
  // ==========================================================

  if (
    overview.value &&
    metricsCanvas.value
  ) {

    await renderMetricsChart()

  } else {

    console.warn(
      '⚠️ Métricas não renderizadas'
    )

  }

  // ==========================================================
  // GRÁFICO DE HISTÓRICO
  // ==========================================================

  if (
    historyCanvas.value &&
    history.value.length
  ) {

    await renderHistoryChart()

  } else {

    console.warn(
      '⚠️ Histórico não renderizado'
    )

  }

  // ==========================================================
  // GRÁFICO DE ETAPAS
  // ==========================================================

  if (
    dealSummary.value &&
    stagesCanvas.value
  ) {

    await renderStagesChart()

  } else {

    console.warn(
      '⚠️ Etapas não renderizadas'
    )

  }

  // ==========================================================
  // GRÁFICO DE STATUS
  // ==========================================================

  if (
    dealSummary.value &&
    statusCanvas.value
  ) {

    await renderStatusChart()

  } else {

    console.warn(
      '⚠️ Status não renderizado'
    )

  }

  console.log(
    '================================'
  )

  console.log(
    '✅ FINALIZADA RENDERIZAÇÃO DOS GRÁFICOS'
  )

  console.log(
    '================================'
  )
}

// ============================================================
// ON MOUNT
// ============================================================

onMounted(async () => {

  console.log(
    '🚀 HubSpot Dashboard iniciado'
  )

  console.log(
    'REVERB KEY:',
    import.meta.env
      .VITE_REVERB_APP_KEY
  )

  try {

    // ========================================================
    // VERIFICAR STATUS DO HUBSPOT
    // ========================================================

    const { data } =
      await api.get(
        '/hubspot/status'
      )

    connected.value =
      Boolean(
        data?.connected
      )

    account.value =
      data?.account ?? null

    // ========================================================
    // HUBSPOT CONECTADO
    // ========================================================

    if (connected.value) {

      console.log(
        '✅ HubSpot conectado'
      )

      console.log(
        '📡 Carregando dados do dashboard...'
      )

      // ======================================================
      // CARREGAR TODOS OS DADOS
      // ======================================================

      await Promise.all([
        loadOverview(),
        loadHistory(),
        loadDealSummary()
      ])

      console.log(
        '================================'
      )

      console.log(
        '📦 TODOS OS DADOS CARREGADOS'
      )

      console.log(
        'Overview:',
        overview.value
      )

      console.log(
        'Deal Summary:',
        dealSummary.value
      )

      console.log(
        'Histórico:',
        history.value
      )

      console.log(
        '================================'
      )

      // ======================================================
      // LIBERAR TEMPLATE
      //
      // A partir daqui os blocos dependentes de estado
      // podem ser renderizados pelo Vue.
      // ======================================================

      loading.value = false

      // ======================================================
      // AGUARDAR VUE
      // ======================================================

      await nextTick()

      await nextTick()

      // ======================================================
      // VERIFICAR CANVAS
      // ======================================================

      console.log(
        '================================'
      )

      console.log(
        '🎨 VERIFICANDO CANVAS APÓS RENDER DO VUE'
      )

      console.log(
        'metricsCanvas:',
        metricsCanvas.value
      )

      console.log(
        'historyCanvas:',
        historyCanvas.value
      )

      console.log(
        'stagesCanvas:',
        stagesCanvas.value
      )

      console.log(
        'statusCanvas:',
        statusCanvas.value
      )

      console.log(
        '================================'
      )

      // ======================================================
      // CRIAR GRÁFICOS
      // ======================================================

      await renderAllCharts()

    } else {

      console.log(
        '⚠️ HubSpot não conectado'
      )

      loading.value = false
    }

  } catch (err) {

    console.error(
      'Erro ao verificar status:',
      err
    )

    error.value =
      '❌ Não foi possível verificar o status do HubSpot'

    loading.value = false
  }

  // ==========================================================
  // ECHO / REVERB
  // ==========================================================

  try {

    if (
      echo &&
      typeof echo.private ===
        'function'
    ) {

      echoChannel =
        echo
          .private('user.1')
          .listen(
            '.test.event',
            async (event) => {

              console.log(
                '🔥 Evento recebido:',
                event
              )

              await refreshDashboard()
            }
          )

      console.log(
        '✅ Echo configurado'
      )

    }

  } catch (err) {

    /*
     * O Echo/Reverb é opcional.
     *
     * Se o Reverb estiver desligado,
     * o dashboard continua funcionando.
     */

    console.warn(
      '⚠️ Echo/Reverb não disponível:',
      err
    )
  }
})

// ============================================================
// ON BEFORE UNMOUNT
// ============================================================

onBeforeUnmount(() => {

  console.log(
    '🧹 Desmontando HubSpot Dashboard'
  )

  destroyCharts()

  try {

    if (
      echoChannel &&
      typeof echoChannel.stopListening ===
        'function'
    ) {

      echoChannel.stopListening(
        '.test.event'
      )
    }

  } catch (err) {

    console.warn(
      'Erro ao limpar Echo:',
      err
    )
  }
})
</script>

<template>

  <div class="page">

    <div class="card">

      <!-- ================================================= -->
      <!-- HEADER -->
      <!-- ================================================= -->

      <div class="header">

        <img
          src="https://www.hubspot.com/hubfs/assets/hubspot.com/style-guide/brand-guidelines/guidelines_the-logo.svg"
          alt="HubSpot Logo"
        />

        <h1>
          Integração HubSpot
        </h1>

        <p>
          Conecte sua plataforma ao HubSpot
          para sincronizar contatos, empresas
          e negócios.
        </p>

      </div>

      <div class="content">

        <!-- ================================================= -->
        <!-- LOADING -->
        <!-- ================================================= -->

        <div
          v-if="loading"
          class="badge loading"
        >
          ⏳ Verificando conexão...
        </div>

        <!-- ================================================= -->
        <!-- ERROR -->
        <!-- ================================================= -->

        <div
          v-else-if="error"
          class="badge error"
        >

          <span>
            {{ error }}
          </span>

          <button
            class="retry-button"
            @click="refreshDashboard"
          >
            Tentar novamente
          </button>

        </div>

        <!-- ================================================= -->
        <!-- NÃO CONECTADO -->
        <!-- ================================================= -->

        <div
          v-else-if="!connected"
          class="center"
        >

          <div class="badge warning">
            ⚠️ HubSpot não conectado
          </div>

          <button
            class="btn primary"
            @click="connect"
          >
            🔐 Conectar HubSpot
          </button>

        </div>

        <!-- ================================================= -->
        <!-- CONECTADO -->
        <!-- ================================================= -->

        <div v-else>

          <!-- ================================================= -->
          <!-- TOP -->
          <!-- ================================================= -->

          <div class="top-bar">

            <div class="badge success">
              ✅ HubSpot conectado
            </div>

            <button
              class="btn refresh"
              :disabled="refreshing"
              @click="refreshDashboard"
            >

              <span v-if="!refreshing">
                🔄 Atualizar dados
              </span>

              <span v-else>
                ⏳ Atualizando...
              </span>

            </button>

          </div>

          <!-- ================================================= -->
          <!-- CONTA -->
          <!-- ================================================= -->

          <div class="account-box">

            <div class="account-title">

              <span>
                🏢
              </span>

              <strong>
                Informações da conta
              </strong>

            </div>

            <div class="account-grid">

              <p>

                <strong>
                  Conta:
                </strong>

                {{
                  overview?.company_name
                  ?? account?.company_name
                  ?? platformName
                }}

              </p>

              <p>

                <strong>
                  Portal ID:
                </strong>

                {{
                  overview?.portal_id
                  ?? account?.portal_id
                  ?? '—'
                }}

              </p>

              <p>

                <strong>
                  Região:
                </strong>

                {{
                  overview?.region
                  ?? account?.region
                  ?? '—'
                }}

              </p>

              <p>

                <strong>
                  Timezone:
                </strong>

                {{
                  overview?.timezone
                  ?? account?.timezone
                  ?? '—'
                }}

              </p>

            </div>

          </div>

          <!-- ================================================= -->
          <!-- VISÃO GERAL -->
          <!-- ================================================= -->

          <div class="section-title">

            <h2>
              Visão geral
            </h2>

          </div>

          <div class="metrics">

            <div class="metric-card contacts">

              <span class="metric-icon">
                👥
              </span>

              <div>

                <strong>
                  {{
                    formatNumber(
                      animatedContacts
                    )
                  }}
                </strong>

                <small>
                  Contatos
                </small>

              </div>

            </div>

            <div class="metric-card companies">

              <span class="metric-icon">
                🏢
              </span>

              <div>

                <strong>
                  {{
                    formatNumber(
                      animatedCompanies
                    )
                  }}
                </strong>

                <small>
                  Empresas
                </small>

              </div>

            </div>

            <div class="metric-card deals">

              <span class="metric-icon">
                💼
              </span>

              <div>

                <strong>
                  {{
                    formatNumber(
                      animatedDeals
                    )
                  }}
                </strong>

                <small>
                  Negócios
                </small>

              </div>

            </div>

            <div
              v-if="dealSummary"
              class="metric-card money"
            >

              <span class="metric-icon">
                💰
              </span>

              <div>

                <strong>
                  {{
                    formatCurrency(
                      dealSummary.total_amount
                    )
                  }}
                </strong>

                <small>
                  Valor total
                </small>

              </div>

            </div>

          </div>

          <!-- ================================================= -->
          <!-- INDICADORES -->
          <!-- ================================================= -->

          <div
            v-if="dealSummary"
            class="section-title"
          >

            <h2>
              Indicadores comerciais
            </h2>

          </div>

          <div
            v-if="dealSummary"
            class="commercial-grid"
          >

            <div class="commercial-card">

              <span>
                🎯
              </span>

              <div>

                <small>
                  Ticket médio
                </small>

                <strong>
                  {{
                    formatCurrency(
                      dealSummary.average_ticket
                    )
                  }}
                </strong>

              </div>

            </div>

            <div class="commercial-card won">

              <span>
                🟢
              </span>

              <div>

                <small>
                  Negócios ganhos
                </small>

                <strong>
                  {{
                    formatNumber(
                      dealSummary.won
                    )
                  }}
                </strong>

                <em>
                  {{
                    dealSummary.win_rate ?? 0
                  }}%
                </em>

              </div>

            </div>

            <div class="commercial-card lost">

              <span>
                🔴
              </span>

              <div>

                <small>
                  Negócios perdidos
                </small>

                <strong>
                  {{
                    formatNumber(
                      dealSummary.lost
                    )
                  }}
                </strong>

                <em>
                  {{
                    dealSummary.loss_rate ?? 0
                  }}%
                </em>

              </div>

            </div>

            <div class="commercial-card open">

              <span>
                🔵
              </span>

              <div>

                <small>
                  Negócios em aberto
                </small>

                <strong>
                  {{
                    formatNumber(
                      dealSummary.open
                    )
                  }}
                </strong>

              </div>

            </div>

          </div>

          <!-- ================================================= -->
          <!-- VALORES -->
          <!-- ================================================= -->

          <div
            v-if="dealSummary"
            class="amount-grid"
          >

            <div class="amount-card">

              <span>
                Valor ganho
              </span>

              <strong
                class="text-success"
              >
                {{
                  formatCurrency(
                    dealSummary.won_amount
                  )
                }}
              </strong>

            </div>

            <div class="amount-card">

              <span>
                Valor perdido
              </span>

              <strong
                class="text-danger"
              >
                {{
                  formatCurrency(
                    dealSummary.lost_amount
                  )
                }}
              </strong>

            </div>

            <div class="amount-card">

              <span>
                Valor em aberto
              </span>

              <strong
                class="text-primary"
              >
                {{
                  formatCurrency(
                    dealSummary.open_amount
                  )
                }}
              </strong>

            </div>

          </div>

          <!-- ================================================= -->
          <!-- GRÁFICOS -->
          <!-- ================================================= -->

          <div class="section-title">

            <h2>
              Análise dos dados
            </h2>

          </div>

          <div class="charts-grid">

            <!-- ================================================= -->
            <!-- DISTRIBUIÇÃO -->
            <!-- ================================================= -->

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

            <!-- ================================================= -->
            <!-- STATUS -->
            <!-- ================================================= -->

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

            <!-- ================================================= -->
            <!-- ETAPAS -->
            <!-- ================================================= -->

            <div class="chart-card wide">

              <div class="chart-header">

                <h3>
                  Negócios por etapa
                </h3>

                <span>
                  Distribuição atual do pipeline
                </span>

              </div>

              <div class="chart-wrapper stages-wrapper">

                <canvas
                  ref="stagesCanvas"
                ></canvas>

              </div>

            </div>

            <!-- ================================================= -->
            <!-- HISTÓRICO -->
            <!-- ================================================= -->

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

          <!-- ================================================= -->
          <!-- NEGÓCIOS -->
          <!-- ================================================= -->

          <div
            v-if="
              dealSummary &&
              dealSummary.deals &&
              dealSummary.deals.length
            "
            class="section-title deals-title"
          >

            <div>

              <h2>
                Negócios do HubSpot
              </h2>

              <p>
                Dados recebidos diretamente
                da API do HubSpot
              </p>

            </div>

            <span class="total-badge">

              {{
                dealSummary.deals.length
              }}

              negócios

            </span>

          </div>

          <!-- ================================================= -->
          <!-- TABELA -->
          <!-- ================================================= -->

          <div
            v-if="
              dealSummary &&
              dealSummary.deals &&
              dealSummary.deals.length
            "
            class="table-container"
          >

            <table>

              <thead>

                <tr>

                  <th>
                    Negócio
                  </th>

                  <th>
                    Valor
                  </th>

                  <th>
                    Etapa
                  </th>

                  <th>
                    Pipeline
                  </th>

                  <th>
                    Data de fechamento
                  </th>

                </tr>

              </thead>

              <tbody>

                <tr
                  v-for="deal in dealSummary.deals"
                  :key="deal.id"
                >

                  <td>

                    <strong>
                      {{
                        deal.properties?.dealname
                        ?? 'Sem nome'
                      }}
                    </strong>

                    <small>
                      ID: {{ deal.id }}
                    </small>

                  </td>

                  <td class="amount">

                    {{
                      formatCurrency(
                        deal.properties?.amount
                      )
                    }}

                  </td>

                  <td>

                    <span
                      class="stage-badge"
                      :class="{
                        won:
                          deal.properties?.dealstage
                          === 'closedwon',

                        lost:
                          deal.properties?.dealstage
                          === 'closedlost'
                      }"
                    >

                      {{
                        getStageName(
                          deal.properties?.dealstage
                        )
                      }}

                    </span>

                  </td>

                  <td>

                    {{
                      deal.properties?.pipeline
                      ?? '—'
                    }}

                  </td>

                  <td>

                    {{
                      formatDate(
                        deal.properties?.closedate
                      )
                    }}

                  </td>

                </tr>

              </tbody>

            </table>

          </div>

          <!-- ================================================= -->
          <!-- AÇÕES -->
          <!-- ================================================= -->

          <div class="actions">

            <button
              class="btn secondary"
              :disabled="importing"
              @click="importContacts"
            >

              <span v-if="!importing">
                📥 Importar Contatos
              </span>

              <span v-else>
                ⏳ Importando...
              </span>

            </button>

            <button
              class="btn danger"
              @click="disconnect"
            >
              🔌 Desconectar HubSpot
            </button>

          </div>

        </div>

      </div>

      <!-- ================================================= -->
      <!-- FOOTER -->
      <!-- ================================================= -->

      <div class="footer">

        <small>
          Integração segura via OAuth 2.0 · HubSpot API
        </small>

      </div>

    </div>

  </div>

</template>

<style scoped>

.page {
  min-height: 100vh;

  display: flex;

  justify-content: center;

  align-items: flex-start;

  background:
    linear-gradient(
      135deg,
      #0f172a,
      #1e293b
    );

  font-family:
    system-ui,
    -apple-system,
    BlinkMacSystemFont,
    "Segoe UI",
    sans-serif;

  padding: 24px;
}

.card {
  width: 100%;

  max-width: 1400px;

  padding: 32px;

  border-radius: 20px;

  background: white;

  box-shadow:
    0 20px 40px
    rgba(0, 0, 0, 0.12);

  display: flex;

  flex-direction: column;

  gap: 24px;

  min-height: 90vh;
}

/* ========================================================= */
/* HEADER */
/* ========================================================= */

.header {
  display: flex;

  flex-direction: column;

  align-items: center;

  text-align: center;
}

.header img {
  width: 60px;

  margin-bottom: 12px;
}

.header h1 {
  font-size: 28px;

  color: #33475b;

  margin: 0 0 6px;
}

.header p {
  font-size: 16px;

  color: #516f90;

  margin: 0;
}

/* ========================================================= */
/* BADGES */
/* ========================================================= */

.badge {
  display: inline-flex;

  align-items: center;

  justify-content: center;

  width: fit-content;

  padding: 10px 16px;

  border-radius: 999px;

  font-weight: 600;

  margin-bottom: 18px;
}

.badge.loading {
  background: #eff6ff;

  color: #1d4ed8;
}

.badge.error {
  background: #fef2f2;

  color: #b91c1c;

  gap: 12px;
}

.badge.warning {
  background: #fff7ed;

  color: #c2410c;
}

.badge.success {
  background: #ecfdf5;

  color: #047857;

  margin-bottom: 0;
}

.retry-button {
  border: none;

  background: #b91c1c;

  color: white;

  padding: 6px 10px;

  border-radius: 6px;

  cursor: pointer;
}

/* ========================================================= */
/* CENTER */
/* ========================================================= */

.center {
  text-align: center;

  padding: 50px 20px;
}

/* ========================================================= */
/* TOP BAR */
/* ========================================================= */

.top-bar {
  display: flex;

  align-items: center;

  justify-content: space-between;

  gap: 16px;

  margin-bottom: 18px;
}

/* ========================================================= */
/* ACCOUNT */
/* ========================================================= */

.account-box {
  background: #f8fafc;

  border:
    1px solid #e2e8f0;

  border-radius: 14px;

  padding: 20px;

  margin-bottom: 24px;
}

.account-title {
  display: flex;

  align-items: center;

  gap: 8px;

  margin-bottom: 16px;

  color: #33475b;
}

.account-grid {
  display: grid;

  grid-template-columns:
    repeat(4, 1fr);

  gap: 12px;
}

.account-grid p {
  margin: 0;

  padding: 12px;

  background: white;

  border-radius: 8px;

  color: #516f90;

  font-size: 14px;
}

/* ========================================================= */
/* SECTION */
/* ========================================================= */

.section-title {
  margin:
    28px 0
    14px;
}

.section-title h2 {
  margin: 0;

  font-size: 21px;

  color: #33475b;
}

/* ========================================================= */
/* METRICS */
/* ========================================================= */

.metrics {
  display: grid;

  grid-template-columns:
    repeat(4, 1fr);

  gap: 16px;
}

.metric-card {
  border-radius: 14px;

  padding: 22px;

  display: flex;

  align-items: center;

  gap: 15px;

  border:
    1px solid transparent;

  min-height: 100px;
}

.metric-card.contacts {
  background: #eff6ff;

  border-color: #dbeafe;
}

.metric-card.companies {
  background: #ecfdf5;

  border-color: #d1fae5;
}

.metric-card.deals {
  background: #fffbeb;

  border-color: #fef3c7;
}

.metric-card.money {
  background: #f5f3ff;

  border-color: #ede9fe;
}

.metric-icon {
  font-size: 32px;
}

.metric-card strong {
  display: block;

  font-size: 23px;

  color: #1e293b;

  margin-bottom: 4px;
}

.metric-card small {
  color: #64748b;

  font-weight: 500;
}

/* ========================================================= */
/* COMMERCIAL */
/* ========================================================= */

.commercial-grid {
  display: grid;

  grid-template-columns:
    repeat(4, 1fr);

  gap: 16px;
}

.commercial-card {
  background: #f8fafc;

  border:
    1px solid #e2e8f0;

  border-radius: 14px;

  padding: 20px;

  display: flex;

  gap: 14px;

  align-items: center;
}

.commercial-card > span {
  font-size: 28px;
}

.commercial-card small {
  display: block;

  color: #64748b;

  margin-bottom: 5px;
}

.commercial-card strong {
  display: block;

  font-size: 22px;

  color: #1e293b;
}

.commercial-card em {
  font-style: normal;

  font-size: 13px;

  color: #64748b;
}

.commercial-card.won {
  border-left:
    4px solid #10b981;
}

.commercial-card.lost {
  border-left:
    4px solid #ef4444;
}

.commercial-card.open {
  border-left:
    4px solid #3b82f6;
}

/* ========================================================= */
/* VALUES */
/* ========================================================= */

.amount-grid {
  display: grid;

  grid-template-columns:
    repeat(3, 1fr);

  gap: 16px;

  margin-top: 16px;
}

.amount-card {
  padding: 18px;

  border-radius: 12px;

  background: #f8fafc;

  border:
    1px solid #e2e8f0;
}

.amount-card span {
  display: block;

  font-size: 13px;

  color: #64748b;

  margin-bottom: 8px;
}

.amount-card strong {
  font-size: 20px;
}

.text-success {
  color: #059669;
}

.text-danger {
  color: #dc2626;
}

.text-primary {
  color: #2563eb;
}

/* ========================================================= */
/* CHARTS */
/* ========================================================= */

.charts-grid {
  display: grid;

  grid-template-columns:
    repeat(2, 1fr);

  gap: 20px;
}

.chart-card {
  background: white;

  border:
    1px solid #e2e8f0;

  border-radius: 14px;

  padding: 20px;

  box-shadow:
    0 4px 12px
    rgba(15, 23, 42, 0.04);

  min-width: 0;
}

.chart-card.wide {
  grid-column:
    span 2;
}

.chart-header {
  margin-bottom: 14px;
}

.chart-header h3 {
  margin:
    0 0 5px;

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

/* ========================================================= */
/* TABLE */
/* ========================================================= */

.deals-title {
  display: flex;

  justify-content: space-between;

  align-items: center;
}

.deals-title p {
  margin:
    5px 0 0;

  font-size: 13px;

  color: #94a3b8;
}

.total-badge {
  background: #eff6ff;

  color: #1d4ed8;

  padding: 8px 14px;

  border-radius: 999px;

  font-size: 13px;

  font-weight: 600;
}

.table-container {
  overflow-x: auto;

  border:
    1px solid #e2e8f0;

  border-radius: 14px;
}

table {
  width: 100%;

  border-collapse: collapse;

  min-width: 800px;
}

thead {
  background: #f8fafc;
}

th {
  text-align: left;

  padding: 14px;

  font-size: 13px;

  color: #64748b;

  border-bottom:
    1px solid #e2e8f0;
}

td {
  padding: 14px;

  border-bottom:
    1px solid #f1f5f9;

  font-size: 14px;

  color: #475569;
}

tbody tr:hover {
  background: #f8fafc;
}

td strong {
  display: block;

  color: #334155;
}

td small {
  display: block;

  color: #94a3b8;

  margin-top: 3px;
}

td.amount {
  font-weight: 700;

  color: #334155;
}

.stage-badge {
  display: inline-block;

  padding: 5px 9px;

  border-radius: 999px;

  background: #eff6ff;

  color: #2563eb;

  font-size: 12px;

  font-weight: 600;
}

.stage-badge.won {
  background: #ecfdf5;

  color: #047857;
}

.stage-badge.lost {
  background: #fef2f2;

  color: #b91c1c;
}

/* ========================================================= */
/* ACTIONS */
/* ========================================================= */

.actions {
  display: flex;

  gap: 16px;

  flex-wrap: wrap;

  margin-top: 24px;
}

.btn {
  padding: 14px 18px;

  font-size: 15px;

  font-weight: 600;

  border-radius: 10px;

  cursor: pointer;

  border: none;

  transition: 0.2s;

  min-height: 48px;
}

.btn:disabled {
  opacity: 0.6;

  cursor: not-allowed;
}

.btn.primary {
  background: #ff7a59;

  color: white;
}

.btn.primary:hover {
  background: #ff5c35;
}

.btn.secondary {
  flex: 1;

  background: #0091ae;

  color: white;
}

.btn.secondary:hover {
  background: #007a92;
}

.btn.danger {
  flex: 1;

  background: #e53935;

  color: white;
}

.btn.danger:hover {
  background: #b71c1c;
}

.btn.refresh {
  background: #33475b;

  color: white;
}

.btn.refresh:hover {
  background: #253747;
}

/* ========================================================= */
/* FOOTER */
/* ========================================================= */

.footer {
  text-align: center;

  font-size: 12px;

  color: #7c98b6;

  margin-top: 24px;
}

/* ========================================================= */
/* RESPONSIVE */
/* ========================================================= */

@media (max-width: 1100px) {

  .metrics,
  .commercial-grid {
    grid-template-columns:
      repeat(2, 1fr);
  }

  .account-grid {
    grid-template-columns:
      repeat(2, 1fr);
  }

  .charts-grid {
    grid-template-columns:
      1fr;
  }

  .chart-card.wide {
    grid-column:
      span 1;
  }

}

@media (max-width: 700px) {

  .page {
    padding: 10px;
  }

  .card {
    padding: 18px;

    border-radius: 14px;
  }

  .metrics,
  .commercial-grid,
  .amount-grid,
  .account-grid {
    grid-template-columns:
      1fr;
  }

  .top-bar {
    align-items: stretch;

    flex-direction: column;
  }

  .badge.success {
    width: 100%;
  }

  .btn.refresh {
    width: 100%;
  }

  .charts-grid {
    grid-template-columns:
      1fr;
  }

  .chart-card.wide {
    grid-column:
      span 1;
  }

  .chart-wrapper {
    height: 280px;

    min-height: 280px;
  }

  .stages-wrapper {
    height: 320px;

    min-height: 320px;
  }

  .actions {
    flex-direction: column;
  }

  .btn.secondary,
  .btn.danger {
    width: 100%;
  }

}

</style>