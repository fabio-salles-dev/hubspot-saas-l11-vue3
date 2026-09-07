<script setup>
import {
  ref,
  onMounted,
  onBeforeUnmount
} from "vue";

import {
  getHubspotStatus,
  getHubspotOverview,
  getHubspotHistory,
  getHubspotDealSummary,
  importHubspotContacts,
  disconnectHubspot,
  getHubspotConnectUrl
} from "../modules/hubspot/hubspot.service";

import HubspotStatus from "../modules/hubspot/HubspotStatus.vue";
import HubspotMetrics from "../modules/hubspot/HubspotMetrics.vue";
import HubspotCharts from "../modules/hubspot/HubspotCharts.vue";
import HubspotDealsTable from "../modules/deals/DealsTable.vue";

// ============================================================
// STATE
// ============================================================

const loading = ref(true);
const importing = ref(false);
const connected = ref(false);
const error = ref(null);

const account = ref(null);
const overview = ref(null);
const history = ref([]);
const dealSummary = ref(null);

const animatedContacts = ref(0);
const animatedCompanies = ref(0);
const animatedDeals = ref(0);

const platformName = ref("DevNest HubSpot Account");

// ============================================================
// HUBSPOT ACTIONS
// ============================================================

const connect = () => {
  window.location.href =
    getHubspotConnectUrl();
};

const importContacts = async () => {
  importing.value = true;
  error.value = null;

  try {
    await importHubspotContacts();

    await refreshDashboard();
  } catch (e) {
    console.error(
      "Erro ao importar contatos:",
      e
    );

    error.value =
      "Não foi possível importar os dados do HubSpot.";
  } finally {
    importing.value = false;
  }
};

const disconnect = async () => {
  try {
    await disconnectHubspot();

    resetDashboard();
  } catch (e) {
    console.error(
      "Erro ao desconectar:",
      e
    );

    error.value =
      "Não foi possível desconectar o HubSpot.";
  }
};

// ============================================================
// RESET
// ============================================================

const resetDashboard = () => {
  connected.value = false;

  account.value = null;
  overview.value = null;
  history.value = [];
  dealSummary.value = null;

  animatedContacts.value = 0;
  animatedCompanies.value = 0;
  animatedDeals.value = 0;

  error.value = null;
};

// ============================================================
// LOAD OVERVIEW
// ============================================================

const loadOverview = async () => {
  try {
    const { data } =
      await getHubspotOverview();

    if (!data) {
      overview.value = null;

      animatedContacts.value = 0;
      animatedCompanies.value = 0;
      animatedDeals.value = 0;

      return;
    }

    overview.value = data;

    animatedContacts.value =
      Number(
        data.objects?.contacts
      ) || 0;

    animatedCompanies.value =
      Number(
        data.objects?.companies
      ) || 0;

    animatedDeals.value =
      Number(
        data.objects?.deals
      ) || 0;

  } catch (e) {
    console.error(
      "Erro ao carregar overview:",
      e
    );

    overview.value = null;

    animatedContacts.value = 0;
    animatedCompanies.value = 0;
    animatedDeals.value = 0;

    throw e;
  }
};

// ============================================================
// LOAD HISTORY
// ============================================================

const loadHistory = async () => {
  try {
    const { data } =
      await getHubspotHistory();

    history.value =
      Array.isArray(data)
        ? data
        : [];

  } catch (e) {
    console.error(
      "Erro ao carregar histórico:",
      e
    );

    history.value = [];

    throw e;
  }
};

// ============================================================
// LOAD DEAL SUMMARY
// ============================================================

const loadDealSummary = async () => {
  try {
    const { data } =
      await getHubspotDealSummary();

    dealSummary.value =
      data ?? null;

  } catch (e) {
    console.error(
      "Erro ao carregar resumo dos negócios:",
      e
    );

    dealSummary.value = null;

    throw e;
  }
};

// ============================================================
// REFRESH
// ============================================================

const refreshDashboard = async () => {
  if (!connected.value) {
    return;
  }

  error.value = null;

  try {
    await Promise.all([
      loadOverview(),
      loadHistory(),
      loadDealSummary()
    ]);

  } catch (e) {
    console.error(
      "Erro ao atualizar dashboard:",
      e
    );

    error.value =
      "Não foi possível atualizar o dashboard.";
  }
};

// ============================================================
// INITIALIZATION
// ============================================================

const initializeDashboard = async () => {
  loading.value = true;
  error.value = null;

  try {
    const { data } =
      await getHubspotStatus();

    connected.value =
      Boolean(
        data?.connected
      );

    account.value =
      data?.account ?? null;

    if (connected.value) {
      await refreshDashboard();
    }

  } catch (e) {
    console.error(
      "Erro ao verificar HubSpot:",
      e
    );

    error.value =
      "Erro ao verificar HubSpot.";

  } finally {
    loading.value = false;
  }
};

// ============================================================
// EXTERNAL REFRESH EVENT
// ============================================================

const handleDashboardRefresh =
  async () => {
    await refreshDashboard();
  };

// ============================================================
// LIFECYCLE
// ============================================================

onMounted(async () => {
  window.addEventListener(
    "refresh-dashboard",
    handleDashboardRefresh
  );

  await initializeDashboard();
});

onBeforeUnmount(() => {
  window.removeEventListener(
    "refresh-dashboard",
    handleDashboardRefresh
  );
});
</script>

<template>
  <div class="admin-layout">
    <div class="dashboard-container">

      <!-- ================================================== -->
      <!-- HEADER -->
      <!-- ================================================== -->

      <header class="dashboard-header">
        <HubspotStatus
          :loading="loading"
          :error="error"
          :connected="connected"
          :account="account"
          :overview="overview"
          :platformName="platformName"
          :importing="importing"
          @connect="connect"
          @import="importContacts"
          @disconnect="disconnect"
          @deals-table-refresh="refreshDashboard"
        />
      </header>

      <!-- ================================================== -->
      <!-- DASHBOARD -->
      <!-- ================================================== -->

      <main
        v-if="!loading"
        class="dashboard-content fade-in"
      >

        <!-- ================================================== -->
        <!-- MÉTRICAS -->
        <!-- ================================================== -->

        <section
          v-if="overview"
          class="section-group"
        >
          <HubspotMetrics
            :contacts="animatedContacts"
            :companies="animatedCompanies"
            :deals="animatedDeals"
          />
        </section>

        <!-- ================================================== -->
        <!-- GRÁFICOS -->
        <!-- ================================================== -->

        <section
          v-if="overview"
          class="section-group chart-wrapper"
        >
          <HubspotCharts
            :overview="overview"
            :history="history"
            :dealSummary="dealSummary"
          />
        </section>

      </main>

      <!-- ================================================== -->
      <!-- LOADING -->
      <!-- ================================================== -->

      <div
        v-else
        class="global-loader"
      >
        <div class="spinner"></div>

        <p>
          Carregando ecossistema...
        </p>
      </div>

    </div>
  </div>
</template>

<style scoped>
.dashboard-container {
  max-width: 1900px;
  margin: 0 auto;
  padding: 20px;
}

.dashboard-header {
  margin-bottom: 20px;
}

.dashboard-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.section-group {
  background: #f1f5f9;
  padding: 20px;
  border-radius: 12px;

  box-shadow:
    0 2px 6px
    rgba(0, 0, 0, 0.05);
}

.chart-wrapper {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.global-loader {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;

  height: 300px;
}

.spinner {
  width: 40px;
  height: 40px;

  border: 4px solid #f3f3f3;
  border-top: 4px solid #1e3a8a;
  border-radius: 50%;

  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}
</style>