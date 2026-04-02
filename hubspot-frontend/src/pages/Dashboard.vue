<script setup>
import { ref, onMounted } from "vue";
import api from "../services/api";

import HubspotStatus from "../modules/hubspot/HubspotStatus.vue";
import HubspotMetrics from "../modules/hubspot/HubspotMetrics.vue";
import HubspotCharts from "../modules/hubspot/HubspotCharts.vue";

import DealsCards from "../modules/deals/DealsCard.vue";
import DealsTable from "../modules/deals/DealsTable.vue";

// ===== STATE =====
const loading = ref(true);
const importing = ref(false);
const connected = ref(false);
const error = ref(null);

const account = ref(null);
const overview = ref(null);
const history = ref([]);

const deals = ref([]);

const animatedContacts = ref(0);
const animatedCompanies = ref(0);
const animatedDeals = ref(0);

const platformName = ref("DevNest HubSpot Account");

// ===== HUBSPOT ACTIONS =====
const connect = () => {
  window.location.href = "http://localhost:8000/api/hubspot/redirect";
};

const importContacts = async () => {
  importing.value = true;
  try {
    await api.post("/hubspot/import");
    await loadOverview();
    await loadHistory();
  } finally {
    importing.value = false;
  }
};

const disconnect = async () => {
  try {
    await api.post("/hubspot/disconnect");
  } catch (e) {
    console.error("Erro ao desconectar:", e);
  }

  // 🔥 RESET CORRETO
  connected.value = false;
  overview.value = null;
  account.value = null;
  history.value = [];

  animatedContacts.value = 0;
  animatedCompanies.value = 0;
  animatedDeals.value = 0;
};

// ===== LOADERS =====
const loadOverview = async () => {
  try {
    const { data } = await api.get("/hubspot/overview-live");

    if (!data) {
      overview.value = null;
      return;
    }

    overview.value = data;

    animatedContacts.value = data.objects?.contacts ?? 0;
    animatedCompanies.value = data.objects?.companies ?? 0;
    animatedDeals.value = data.objects?.deals ?? 0;
  } catch (e) {
    console.error("Erro ao carregar overview:", e);
    overview.value = null;
  }
};

const loadHistory = async () => {
  try {
    const { data } = await api.get("/hubspot/history");
    history.value = data || [];
  } catch (e) {
    console.error("Erro ao carregar histórico:", e);
    history.value = [];
  }
};

const loadDeals = async () => {
  const { data } = await api.get("/deals");
  deals.value = data.data;
};

// ===== INIT =====
onMounted(async () => {
  try {
    const { data } = await api.get("/hubspot/status");
    connected.value = data.connected;
    account.value = data.account;

    if (connected.value) {
      await loadOverview();
      await loadHistory();
    }

    await loadDeals();
  } catch {
    error.value = "Erro ao verificar HubSpot";
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="admin-layout">
    <div class="dashboard-container">

      <!-- HEADER -->
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
        />
      </header>

      <main class="dashboard-content fade-in" v-if="!loading">

        <!-- MÉTRICAS -->
        <section class="section-group">
          <HubspotMetrics
            v-if="overview"
            :contacts="animatedContacts"
            :companies="animatedCompanies"
            :deals="animatedDeals"
          />
        </section>

        <!-- 🔥 GRÁFICOS (FIX PRINCIPAL) -->
        <section
          class="section-group chart-wrapper"
          v-if="overview"
        >
          <HubspotCharts
            :overview="overview"
            :history="history"
          />
        </section>

        <!-- DEALS -->
        <section v-if="deals.length" class="section-group deals-section">
          <div class="section-header">
            <h2>💼 Negócios em Aberto</h2>
            <p>Acompanhamento de pipeline em tempo real</p>
          </div>

          <DealsCards :deals="deals" />

          <div class="shadow-sm table-container">
            <DealsTable :deals="deals" />
          </div>
        </section>

      </main>

      <!-- LOADING -->
      <div v-else class="global-loader">
        <div class="spinner"></div>
        <p>Carregando ecossistema...</p>
      </div>

    </div>
  </div>
</template>