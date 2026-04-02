<script setup>
import { ref, onMounted } from "vue";
import api from "../services/api";

import HubspotStatus from "../modules/hubspot/HubspotStatus.vue";
import HubspotMetrics from "../modules/hubspot/HubspotMetrics.vue";
import HubspotCharts from "../modules/hubspot/HubspotCharts.vue";

import DealsCards from "../modules/deals/DealsCard.vue";
import DealsTable from "../modules/deals/DealsTable.vue";

// ===== STATE (Preservado) =====
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

// ===== HUBSPOT ACTIONS (Preservado) =====
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

  connected.value = false;
  overview.value = null;
  account.value = null;
  history.value = [];

  animatedContacts.value = 0;
  animatedCompanies.value = 0;
  animatedDeals.value = 0;
};

// ===== LOADERS (Preservado) =====
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
  const { data } = await api.get("/hubspot/history");
  history.value = data || [];
};

const loadDeals = async () => {
  const { data } = await api.get("/deals");
  deals.value = data.data;
};

// ===== INIT (Preservado) =====
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
      <!-- Seção Principal: Status e Integração -->
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
        <!-- Métricas Rápidas -->
        <section class="section-group">
          <HubspotMetrics
            v-if="overview"
            :contacts="animatedContacts"
            :companies="animatedCompanies"
            :deals="animatedDeals"
          />
        </section>

        <!-- Gráficos de Evolução -->
        <section
          class="section-group chart-wrapper"
          v-if="overview && history.length"
        >
          <HubspotCharts :overview="overview" :history="history" />
        </section>

        <!-- Gestão de Deals (Negócios) -->
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

      <!-- Estado de Loading Central -->
      <div v-else class="global-loader">
        <div class="spinner"></div>
        <p>Carregando ecossistema...</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 
  Layout Arrojado e Profissional
  Baseado em cores Slate/Indigo (Estilo Tailwind/Modern SaaS)
*/

.admin-layout {
  min-height: 100vh;
  background-color: #0f172a; /* Azul Slate Escuro */
  color: #f1f5f9;
  padding: 40px 20px;
  font-family:
    "Inter",
    system-ui,
    -apple-system,
    sans-serif;
}

.dashboard-container {
  max-width: 1200px;
  margin: 0 auto;
}

.dashboard-header {
  margin-bottom: 32px;
  background: rgba(30, 41, 59, 0.5);
  padding: 24px;
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.section-group {
  margin-bottom: 40px;
}

/* Títulos de Seção */
.section-header {
  margin-bottom: 24px;
}

.section-header h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #fff;
  margin: 0;
}

.section-header p {
  color: #94a3b8;
  font-size: 0.9rem;
  margin-top: 4px;
}

/* Ajustes para Tabelas e Cards de Negócios */
.table-container {
  background: white;
  color: #1e293b;
  border-radius: 12px;
  overflow: hidden;
  margin-top: 24px;
  border: 1px solid #e2e8f0;
}

.deals-section {
  background: rgba(255, 255, 255, 0.02);
  padding: 32px;
  border-radius: 20px;
  border: 1px dashed rgba(255, 255, 255, 0.1);
}

/* Animação de entrada */
.fade-in {
  animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Loader Global */
.global-loader {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 50vh;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(255, 255, 255, 0.1);
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Media Queries para Responsividade */
@media (max-width: 768px) {
  .admin-layout {
    padding: 20px 10px;
  }
  .dashboard-header {
    padding: 16px;
  }
}
</style>
